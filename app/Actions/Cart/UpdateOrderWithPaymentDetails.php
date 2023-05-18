<?php

namespace App\Actions\Cart;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Carbon;
use App\DataTransferObjects\Cart\CompletePaymentData;

class UpdateOrderWithPaymentDetails
{
    public function execute(CompletePaymentData $data, Order $order): void
    {
        $order->update([
            "status" => OrderStatus::PAID->name,
            "payment_date" => Carbon::parse($data->date)->toDateTimeString(),
            "card" => $data->card,
            "total" => $data->total,
            "fees" => $data->total - $order->subtotal,
        ]);
    }
}
