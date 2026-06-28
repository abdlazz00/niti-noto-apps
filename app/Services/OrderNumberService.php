<?php

namespace App\Services;

use App\Models\Order;

class OrderNumberService
{
    public function generate(): string
    {
        $today = now()->format('Ymd');
        $count = Order::whereDate('created_at', today())->count() + 1;
        return sprintf('NNT-%s-%04d', $today, $count);
    }
}
