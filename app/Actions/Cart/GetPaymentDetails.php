<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Enums\CartStatus;
use App\DataTransferObjects\WiPay\WiPayResponse;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class GetPaymentDetails
{
    public function execute(Cart $cart): WiPayResponse
    {
        $amount = $this->getTotalAmount($cart);
        $response = $this->getPaymentLink($cart, $amount);

        $this->updateCardAndOrder($cart, $response);

        return $response;
    }

    private function getTotalAmount(Cart $cart)
    {
        $amount = 0;
        $newOrder = !$cart->order;

        if ($newOrder) {
            foreach ($cart->books as $book) {
                $priceForBook = $book->price * $book->pivot->quantity;
                $amount += $priceForBook;

                $cart->order->books()->attach($book->id, [
                    "quantity" => $book->pivot->quantity,
                    "price" => $priceForBook,
                ]);
            }
        }

        if (!$newOrder) {
            $amount = $cart->order->books->sum("pivot.price");
        }

        return $amount;
    }

    private function getPaymentLink(Cart $cart, float $amount): WiPayResponse
    {
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
                "order_id" => $cart->order->id,
                "origin" => "Book_It_App",
                "response_url" => route("api.cart.complete-payment"),
                "total" => $amount,
            ]),
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($statusCode != 200) {
            throw new InternalErrorException(
                "Something went wrong while processing your payment.",
            );
        }

        $result = json_decode($result, true);

        return WiPayResponse::fromArray($result);
    }

    private function updateCardAndOrder(
        Cart $cart,
        WiPayResponse $response,
    ): void {
        $cart->order->update(["transaction_id" => $response->transactionId]);

        $cart->update(["status" => CartStatus::CLOSED->name]);
    }
}
