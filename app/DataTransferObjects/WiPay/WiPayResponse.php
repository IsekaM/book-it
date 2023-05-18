<?php

namespace App\DataTransferObjects\WiPay;

use App\DataTransferObjects\DataTransferObject;

class WiPayResponse extends DataTransferObject
{
    public ?string $url;

    public ?string $transactionId;
}
