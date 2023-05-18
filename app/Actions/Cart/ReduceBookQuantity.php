<?php

namespace App\Actions\Cart;

use App\Models\Order;

class ReduceBookQuantity
{
    public function execute(Order $order): void
    {
        foreach ($order->books as $book) {
            if ($book->quantity > 0) {
                $book->decrement("quantity");
            }

            if ($book->quantity <= 0) {
                $order->books()->detach($book->id);
            }
        }
    }
}
