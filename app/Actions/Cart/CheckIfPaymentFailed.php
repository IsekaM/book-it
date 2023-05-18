<?php

namespace App\Actions\Cart;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\DataTransferObjects\Cart\CompletePaymentData;

class CheckIfPaymentFailed
{
    public function execute(CompletePaymentData $data, Order $order): void
    {
        if ($data->status === "failed") {
            $order->update(["status" => OrderStatus::PAYMENT_FAILED->name]);

            throw new \Exception("Payment failed.");
        }
    }
}
