<?php

namespace App\Services;

use App\Models\Order;

class OrderNumberService
{
    public function generate(): string
    {
        $today = now()->format('Ymd');
        $count = Order::whereDate('created_at', today())->count() + 1;
        $rand  = strtoupper(bin2hex(random_bytes(2)));
        return sprintf('NNT-%s-%04d-%s', $today, $count, $rand);
    }
}
