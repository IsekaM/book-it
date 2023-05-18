<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\DataTransferObjects\WiPay\WiPayResponse;

class CheckoutItemsInCart
{
    public function __construct(
        private readonly CheckIfItemsInCart $checkIfItemsInCart,
        private readonly CheckIfOrderIsProcessed $checkIfOrderIsProcessed,
        private readonly GetPaymentDetails $getPaymentDetails,
    ) {
    }

    public function execute(Cart $cart): WiPayResponse
    {
        $this->checkIfItemsInCart->execute($cart);
        $this->checkIfOrderIsProcessed->execute($cart);
        return $this->getPaymentDetails->execute($cart);
    }
}
