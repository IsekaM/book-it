<?php

namespace App\Actions\Cart;

use App\Models\Order;
use App\Enums\OrderStatus;

class CheckIfOrderIsProcessed
{
    public function execute(Order $order): void
    {
        if ($order->status != OrderStatus::PROCESSING->name) {
            throw new \Exception("Order has already been processed.");
        }
    }
}
