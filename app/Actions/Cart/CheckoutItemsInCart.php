<?php

namespace App\Actions\Cart;

use App\Models\Cart;

class CheckoutItemsInCart
{
    public function __construct(
        private readonly CheckIfItemsInCart $checkIfItemsInCart,
        private readonly CheckIfOrderIsProcessed $checkIfOrderIsProcessed,
    ) {
    }

    public function execute(Cart $cart): void
    {
        $this->checkIfItemsInCart->execute($cart);
        $this->checkIfOrderIsProcessed->execute($cart);
    }
}
