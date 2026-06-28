<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class DisplayController extends Controller
{
    public function index(): Response
    {
        $readyOrders = Order::where('status', 'siap_diambil')
            ->with('table')
            ->orderBy('updated_at')
            ->get()
            ->map(fn ($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'table_number' => $o->table->number,
                'table_name'   => $o->table->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Display/Index', [
            'readyOrders' => $readyOrders,
        ]);
    }
}
