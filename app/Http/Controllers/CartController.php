<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Order;
use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CheckoutRequest;
use App\Actions\Cart\CheckoutItemsInCart;
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
        $cart = Cart::firstOrCreate([
            "user_id" => $request->user()->id,
            "status" => CartStatus::OPENED->name,
        ]);

        $cart->books()->syncWithoutDetaching($book->id);

        $cart->load("books:id,author,title,price,isbn");

        return response()->formattedJson($cart);
    }

    public function show(Request $request)
    {
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
        $cart = Cart::firstOrCreate([
            "user_id" => $request->user()->id,
            "status" => CartStatus::OPENED->name,
        ]);

        $cart->books()->detach($book->id);

        $cart->load("books:id,author,title,price,isbn");

        return response()->formattedJson($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function checkout(
        CheckoutRequest $request,
        Cart $cart,
        CheckoutItemsInCart $checkoutItemsInCart,
    ) {
        try {
            $cart->load(["order", "books"]);

            if (!$cart->order) {
                $cart->order = Order::create(
                    $request->validated() + ["cart_id" => $cart->id],
                );
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

    public function completePayment(Request $request)
    {
        $transactionId = $request->query("transaction_id");
        $orderId = $request->query("order_id");
        $date = $request->query("date");
        $card = $request->query("card");
        $status = $request->query("status");
        $total = $request->query("total");
        $order = Order::where("transaction_id", $transactionId)
            ->where("id", $orderId)
            ->first();

        if (
            !$transactionId ||
            !$orderId ||
            !$date ||
            !$order ||
            !$status ||
            !$total
        ) {
            return response()->formattedJson(
                null,
                Response::HTTP_BAD_REQUEST,
                "Important data missing.",
            );
        }

        if ($order->status !== OrderStatus::PROCESSING->name) {
            return response()->formattedJson(
                null,
                Response::HTTP_BAD_REQUEST,
                "This order has already been processed.",
            );
        }

        if ($status === "failed") {
            $order->update(["status" => OrderStatus::PAYMENT_FAILED->name]);

            return response()->formattedJson(
                null,
                Response::HTTP_BAD_REQUEST,
                "Payment failed.",
            );
        }

        $order->update([
            "status" => OrderStatus::PAID->name,
            "payment_date" => Carbon::parse($date)->toDateTimeString(),
            "card" => $card,
            "total" => $total,
            "fees" => $total - $order->subtotal,
        ]);

        return response()->formattedJson($order);
    }
}
