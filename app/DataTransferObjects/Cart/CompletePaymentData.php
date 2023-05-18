<?php

namespace App\DataTransferObjects\Cart;

use App\DataTransferObjects\DataTransferObject;

class CompletePaymentData extends DataTransferObject
{
    public ?string $card;

    public ?string $date;

    public ?string $orderId;

    public ?string $total;

    public ?string $status;

    public ?string $transactionId;
}
