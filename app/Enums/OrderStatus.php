<?php

namespace App\Enums;

enum OrderStatus
{
    case PROCESSING;

    case PAID;

    case PAYMENT_FAILED;
}
