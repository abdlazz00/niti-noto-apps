<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\ShiftRepository;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private ShiftRepository $shiftRepository) {}

    public function index(): Response
    {
        $today = Carbon::today();
        $shift = $this->shiftRepository->findActiveForCashier(auth()->id());

        $revenueShift = $shift
            ? (float) Order::where('shift_id', $shift->id)->where('status', 'selesai')->sum('total')
            : 0.0;

        return Inertia::render('Cashier/Dashboard', [
            'shift'  => $shift ? [
                'id'         => $shift->id,
                'started_at' => $shift->started_at->format('H:i'),
            ] : null,
            'stats'  => [
                'orders_today'    => Order::whereDate('created_at', $today)->count(),
                'completed_today' => Order::where('status', 'selesai')->whereDate('created_at', $today)->count(),
                'active_orders'   => Order::whereIn('status', ['menunggu', 'diterima', 'sedang_dibuat'])->count(),
                'revenue_shift'   => $revenueShift,
            ],
        ]);
    }
}
