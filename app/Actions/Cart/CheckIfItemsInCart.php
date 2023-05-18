<?php

namespace App\Actions\Cart;

use App\Models\Cart;

class CheckIfItemsInCart
{
    public function execute(Cart $cart)
    {
        if ($cart->books->isEmpty()) {
            throw new \Exception("No items in cart.");
        }
    }
}
