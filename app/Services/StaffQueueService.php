<?php

namespace App\Services;

use App\Events\OrderReadyForPickup;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Repositories\OrderRepository;

class StaffQueueService
{
    // Valid transitions staff can make
    private const TRANSITIONS = [
        'diterima'      => 'sedang_dibuat',
        'sedang_dibuat' => 'siap_diambil',
    ];

    public function __construct(private OrderRepository $orderRepository) {}

    public function getQueue(): array
    {
        $orders = $this->orderRepository->getByStatuses(['diterima', 'sedang_dibuat']);

        $map = fn (Order $o) => [
            'id'           => $o->id,
            'order_number' => $o->order_number,
            'table_number' => $o->table->number,
            'table_name'   => $o->table->name,
            'status'       => $o->status,
            'notes'        => $o->notes,
            'created_at'   => $o->created_at->format('H:i'),
            'next_status'  => self::TRANSITIONS[$o->status] ?? null,
            'items'        => $o->items->map(fn ($i) => [
                'name'  => $i->menuItem->name,
                'qty'   => $i->qty,
                'notes' => $i->notes,
            ])->all(),
        ];

        return [
            'diterima'      => $orders->where('status', 'diterima')->map($map)->values()->all(),
            'sedang_dibuat' => $orders->where('status', 'sedang_dibuat')->map($map)->values()->all(),
        ];
    }

    public function updateStatus(Order $order, string $newStatus, int $staffId): Order|string
    {
        $expected = self::TRANSITIONS[$order->status] ?? null;

        if ($expected !== $newStatus) {
            return "Transisi status tidak valid: {$order->status} → {$newStatus}.";
        }

        $this->orderRepository->updateStatus($order, $newStatus);
        $this->orderRepository->createStatusLog($order->id, $newStatus, $staffId);

        $order->load('table');

        broadcast(new OrderStatusUpdated($order, $newStatus));

        if ($newStatus === 'siap_diambil') {
            broadcast(new OrderReadyForPickup($order));
        }

        return $order;
    }
}
