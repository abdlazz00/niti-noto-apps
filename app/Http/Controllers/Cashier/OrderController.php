<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\CashierOrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepository $orderRepository,
        private CashierOrderService $cashierOrderService,
    ) {}

    public function index(): Response
    {
        $mapOrder = fn (Order $o) => [
            'id'           => $o->id,
            'order_number' => $o->order_number,
            'table_number' => $o->table->number,
            'table_name'   => $o->table->name,
            'status'       => $o->status,
            'total'        => (float) $o->total,
            'notes'        => $o->notes,
            'created_at'   => $o->created_at->format('H:i'),
            'items'        => $o->items->map(fn ($i) => [
                'name'     => $i->menuItem->name,
                'qty'      => $i->qty,
                'price'    => (float) $i->price,
                'subtotal' => (float) ($i->price * $i->qty),
            ])->all(),
        ];

        $orders = $this->orderRepository->getByStatuses(['menunggu', 'siap_diambil']);

        return Inertia::render('Cashier/Order/Index', [
            'pending'   => $orders->where('status', 'menunggu')->map($mapOrder)->values(),
            'readyOrders' => $orders->where('status', 'siap_diambil')->map($mapOrder)->values(),
        ]);
    }

    public function confirm(Order $order): RedirectResponse
    {
        $result = $this->cashierOrderService->confirmOrder($order, auth()->id());

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        return back()->with('success', "Order {$order->order_number} dikonfirmasi.");
    }

    public function complete(Order $order): RedirectResponse
    {
        $result = $this->cashierOrderService->completeOrder($order, auth()->id());

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        return back()->with('success', "Order {$order->order_number} selesai.");
    }
}
