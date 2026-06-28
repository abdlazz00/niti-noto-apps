<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;

class OrderRepository
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function createItems(int $orderId, array $items): void
    {
        $rows = array_map(fn ($item) => [
            'order_id'     => $orderId,
            'menu_item_id' => $item['menu_item_id'],
            'qty'          => $item['qty'],
            'price'        => $item['price'],
            'notes'        => $item['notes'] ?? null,
        ], $items);

        OrderItem::insert($rows);
    }

    public function createStatusLog(int $orderId, string $status, ?int $changedBy = null): void
    {
        OrderStatusLog::create([
            'order_id'   => $orderId,
            'status'     => $status,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }

    public function findWithDetails(Order $order): Order
    {
        return $order->load('table', 'items.menuItem');
    }
}
