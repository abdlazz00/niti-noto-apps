<?php

namespace App\Services;

use App\Events\KitchenNewOrder;
use App\Events\OrderCompleted;
use App\Events\OrderStatusUpdated;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Shift;
use App\Models\Table;
use App\Repositories\OrderRepository;
use App\Repositories\ShiftRepository;

class CashierOrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderNumberService $orderNumberService,
        private ShiftRepository $shiftRepository,
    ) {}

    public function placeOrder(Table $table, array $items, ?string $notes, int $cashierId): Order
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

        $shift = $this->shiftRepository->findActiveForCashier($cashierId);

        $order = $this->orderRepository->create([
            'order_number' => $this->orderNumberService->generate(),
            'table_id'     => $table->id,
            'user_id'      => $cashierId,
            'shift_id'     => $shift?->id,
            'status'       => 'menunggu',
            'total'        => $total,
            'notes'        => $notes,
        ]);

        $this->orderRepository->createItems($order->id, $orderItems);
        $this->orderRepository->createStatusLog($order->id, 'menunggu', $cashierId);

        return $order;
    }

    public function confirmOrder(Order $order, int $cashierId): Order|string
    {
        if ($order->status !== 'menunggu') {
            return 'Order tidak dalam status menunggu.';
        }

        $this->orderRepository->updateStatus($order, 'diterima');

        $shift = $this->shiftRepository->findActiveForCashier($cashierId);
        if ($shift && ! $order->shift_id) {
            $this->orderRepository->assignShift($order, $shift->id);
        }

        $this->orderRepository->createStatusLog($order->id, 'diterima', $cashierId);

        $order->load('table', 'items.menuItem');
        broadcast(new OrderStatusUpdated($order, 'diterima'));
        broadcast(new KitchenNewOrder($order));

        return $order;
    }

    public function completeOrder(Order $order, int $cashierId): Order|string
    {
        if ($order->status !== 'siap_diambil') {
            return 'Order tidak dalam status siap diambil.';
        }

        $this->orderRepository->updateStatus($order, 'selesai');
        $this->orderRepository->createStatusLog($order->id, 'selesai', $cashierId);

        broadcast(new OrderStatusUpdated($order, 'selesai'));
        broadcast(new OrderCompleted($order));

        return $order;
    }
}
