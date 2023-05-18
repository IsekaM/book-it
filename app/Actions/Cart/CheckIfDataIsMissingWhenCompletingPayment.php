<?php

namespace App\Actions\Cart;

use App\Models\Order;
use App\DataTransferObjects\Cart\CompletePaymentData;

class CheckIfDataIsMissingWhenCompletingPayment
{
    public function execute(CompletePaymentData $data, ?Order $order)
    {
        if (
            !$data->transactionId ||
            !$data->orderId ||
            !$data->date ||
            !$order ||
            !$data->status ||
            !$data->total
        ) {
            throw new \Exception("Important data missing from WiPay response.");
        }
    }
}
