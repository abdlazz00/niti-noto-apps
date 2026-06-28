<?php

namespace App\Services;

use App\Events\NewOrderReceived;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use App\Repositories\OrderRepository;

class CustomerOrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderNumberService $orderNumberService,
    ) {}

    public function placeOrder(Table $table, array $items, ?string $notes): Order
    {
        $menuItemIds = array_column($items, 'id');
        $menuItems = MenuItem::active()->whereIn('id', $menuItemIds)->get()->keyBy('id');

        $orderItems = [];
        $total = 0;

        foreach ($items as $item) {
            $menuItem = $menuItems[$item['id']] ?? null;
            if (! $menuItem) continue;

            $price = (float) $menuItem->price;
            $qty   = (int) $item['qty'];

            $orderItems[] = [
                'menu_item_id' => $menuItem->id,
                'qty'          => $qty,
                'price'        => $price,
            ];

            $total += $price * $qty;
        }

        $order = $this->orderRepository->create([
            'order_number' => $this->orderNumberService->generate(),
            'table_id'     => $table->id,
            'user_id'      => auth()->id(),
            'status'       => 'menunggu',
            'total'        => $total,
            'notes'        => $notes,
        ]);

        $this->orderRepository->createItems($order->id, $orderItems);
        $this->orderRepository->createStatusLog($order->id, 'menunggu', auth()->id());

        // Load relations before broadcasting so event has table + items count
        $order->load('table');
        broadcast(new NewOrderReceived($order));

        return $order;
    }
}
