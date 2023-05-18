<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Enums\OrderStatus;

class CheckIfOrderIsProcessed
{
    public function execute(Cart $cart): void
    {
        if ($cart->order->status != OrderStatus::PROCESSING->name) {
            throw new \Exception("Order has already been processed.");
        }
    }
}
