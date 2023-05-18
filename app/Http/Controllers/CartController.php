<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Order;
use App\Enums\CartStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Actions\Cart\CompletePayment;
use App\Http\Requests\CheckoutRequest;
use App\Actions\Cart\CheckoutItemsInCart;
use App\DataTransferObjects\Cart\CompletePaymentData;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class CartController extends Controller
{
    /**
     * Adds a book to the user's cart
     *
     * @param  Request  $request
     * @param  Book  $book
     * @return JsonResponse
     */
    public function add(Request $request, Book $book)
    {
        $this->authorize("add", Cart::class);

        $cart = Cart::firstOrCreate([
            "user_id" => $request->user()->id,
            "status" => CartStatus::OPENED->name,
        ]);

        if ($book->quantity == 0) {
            return response()->formattedJson(
                null,
                Response::HTTP_BAD_REQUEST,
                "Book out of stock.",
            );
        }

        $cart->books()->syncWithoutDetaching($book->id);

        $cart->load("books:id,author,title,price,isbn");

        return response()->formattedJson($cart);
    }

    /**
     * Shows a single
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        $this->authorize("show", Cart::class);

        return response()->formattedJson(
            Cart::with("books:id,author,title,price,isbn")->firstWhere([
                "user_id" => $request->user()->id,
                "status" => CartStatus::OPENED->name,
            ]),
        );
    }

    /**
     * Removes a book from the user's cart
     *
     * @param  Request  $request
     * @param  Book  $book
     * @return JsonResponse
     */
    public function remove(Request $request, Book $book)
    {
        $this->authorize("remove", Cart::class);

        $cart = Cart::firstOrCreate([
            "user_id" => $request->user()->id,
            "status" => CartStatus::OPENED->name,
        ]);

        $cart->books()->detach($book->id);

        $cart->load("books:id,author,title,price,isbn");

        return response()->formattedJson($cart);
    }

    /**
     * Gets a checkout link from WiPay
     *
     * @param  CheckoutRequest  $request
     * @param  Cart  $cart
     * @param  CheckoutItemsInCart  $checkoutItemsInCart
     * @return JsonResponse
     */
    public function checkout(
        CheckoutRequest $request,
        Cart $cart,
        CheckoutItemsInCart $checkoutItemsInCart,
    ) {
        $this->authorize("checkout", $cart);

        try {
            $cart->load(["order", "books"]);

            if (!$cart->order) {
                Order::create($request->validated() + ["cart_id" => $cart->id]);

                $cart->load(["order", "books"]);
            }

            $response = $checkoutItemsInCart->execute($cart);

            return response()->formattedJson(["payment_url" => $response->url]);
        } catch (InternalErrorException $internalErrorException) {
            return response()->formattedJson(
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $internalErrorException->getMessage(),
            );
        } catch (\Exception $exception) {
            return response()->formattedJson(
                null,
                Response::HTTP_BAD_REQUEST,
                $exception->getMessage(),
            );
        }
    }

    /**
     * Marks order as PAID and updates the order's total and fees
     *
     * @param  Request  $request
     * @param  CompletePayment  $completePayment
     * @return JsonResponse
     */
    public function completePayment(
        Request $request,
        CompletePayment $completePayment,
    ) {
        try {
            $data = CompletePaymentData::fromArray($request->query());
            $order = Order::where("transaction_id", $data->transactionId)
                ->where("id", $data->orderId)
                ->first();

            $completePayment->execute($data, $order);

            return response()->formattedJson(
                $order,
                message: "Payment completed successfully.",
            );
        } catch (\Exception $exception) {
            return \response()->formattedJson(
                null,
                Response::HTTP_BAD_REQUEST,
                $exception->getMessage(),
            );
        }
    }
}
