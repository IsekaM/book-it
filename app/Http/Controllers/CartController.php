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
    public function checkout(CheckoutRequest $request, Cart $cart)
    {
        $cart->load(["order", "books"]);

        $newOrder = !$cart->order;
        $order =
            $cart->order ??
            Order::create($request->validated() + ["cart_id" => $cart->id]);
        $amount = 0;

        if ($cart->books->isEmpty()) {
            return response()->formattedJson(
                null,
                Response::HTTP_BAD_REQUEST,
                "No items in cart",
            );
        }

        if ($order->status != OrderStatus::PROCESSING->name) {
            return response()->formattedJson(
                null,
                Response::HTTP_BAD_REQUEST,
                "Order has already been processed.",
            );
        }

        if ($newOrder) {
            foreach ($cart->books as $book) {
                $priceForBook = $book->price * $book->pivot->quantity;
                $amount += $priceForBook;

                $order->books()->attach($book->id, [
                    "quantity" => $book->pivot->quantity,
                    "price" => $priceForBook,
                ]);
            }
        }

        if (!$newOrder) {
            $amount = $order->books->sum("pivot.price");
        }

        $curl = curl_init(
            "https://jm.wipayfinancial.com/plugins/payments/request",
        );

        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/x-www-form-urlencoded",
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                "account_number" => config("wipay.account_number"),
                "avs" => "0",
                "country_code" => "JM",
                "currency" => "JMD",
                "data" => '{"a":"b"}',
                "environment" => "sandbox",
                "fee_structure" => "customer_pay",
                "method" => "credit_card",
                "order_id" => $order->id,
                "origin" => "Book_It_App",
                "response_url" => route("api.cart.complete-payment"),
                "total" => $amount,
            ]),
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($result, true);

        $order->update(["transaction_id" => $result["transaction_id"]]);

        $cart->update(["status" => CartStatus::CLOSED->name]);

        return response()->formattedJson(["payment_url" => $result["url"]]);
    }

    public function completePayment(Request $request)
    {
        $transactionId = $request->query("transaction_id");
        $orderId = $request->query("order_id");
        $date = $request->query("date");
        $card = $request->query("card");
        $status = $request->query("status");
        $order = Order::where("transaction_id", $transactionId)
            ->where("id", $orderId)
            ->first();

        if (!$transactionId || !$orderId || !$date || !$order || !$status) {
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
        ]);

        return response()->formattedJson($order);
    }
}
