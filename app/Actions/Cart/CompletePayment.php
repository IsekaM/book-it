<?php

namespace App\Actions\Cart;

use App\Models\Order;
use App\DataTransferObjects\Cart\CompletePaymentData;

class CompletePayment
{
    public function __construct(
        private readonly CheckIfDataIsMissingWhenCompletingPayment $checkIfDataIsMissingWhenCompletingPayment,
        private readonly CheckIfPaymentFailed $checkIfPaymentFailed,
        private readonly CheckIfOrderIsProcessed $checkIfOrderIsProcessed,
        private readonly UpdateOrderWithPaymentDetails $updateOrderWithPaymentDetails,
    ) {
    }

    public function execute(CompletePaymentData $data, ?Order $order): void
    {
        $this->checkIfDataIsMissingWhenCompletingPayment->execute(
            $data,
            $order,
        );
        $this->checkIfOrderIsProcessed->execute($order);
        $this->checkIfPaymentFailed->execute($data, $order);
        $this->updateOrderWithPaymentDetails->execute($data, $order);
    }
}
