<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Inertia\Inertia;
use Inertia\Response;

class TrackController extends Controller
{
    public function __construct(private OrderRepository $orderRepository) {}

    public function show(Order $order): Response
    {
        $order = $this->orderRepository->findWithDetails($order);

        return Inertia::render('Customer/Order/Track', [
            'order' => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
                'total'        => (float) $order->total,
                'notes'        => $order->notes,
                'table'        => ['number' => $order->table->number, 'name' => $order->table->name, 'qr_code' => $order->table->qr_code],
                'items'        => $order->items->map(fn ($i) => [
                    'name'     => $i->menuItem->name,
                    'qty'      => $i->qty,
                    'price'    => (float) $i->price,
                    'subtotal' => (float) $i->price * $i->qty,
                ])->values()->all(),
            ],
        ]);
    }
}
