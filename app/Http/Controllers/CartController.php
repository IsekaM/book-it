<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Order;
use App\Enums\CartStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Actions\Cart\CompletePayment;
use App\Http\Requests\CheckoutRequest;
use App\Actions\Cart\CheckoutItemsInCart;
use App\DataTransferObjects\Cart\CompletePaymentData;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class CartController extends Controller
{
    /**
     * @lrd:start
     * # Add Book To Cart
     * Adds a book to the user's open/active cart
     * @lrd:end
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
     * @lrd:start
     * # Get A User's Cart
     * Fetches the user's active cart and lists the books inside it
     * @lrd:end
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
     * @lrd:start
     * # Remove Item From Cart
     * Deletes a book from the user's active/open cart
     * @lrd:end
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
     * @lrd:start
     * # Get Payment Link
     * Gets a WiPay link that can be used to pay
     * for books in the specified cart
     * @lrd:end
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
     * @lrd:start
     * # Complete Payment
     * A response route/webhook that WiPay sends a response to
     * after a payment is completed. When this route is accessed with
     * the correct query params, the user's order is marked as PAID and
     * the total and fees are updated
     * @lrd:end
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
