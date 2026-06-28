<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();

        $revenueToday = (float) Order::where('status', 'selesai')
            ->whereDate('created_at', $today)
            ->sum('total');

        $ordersToday = Order::whereDate('created_at', $today)->count();

        $expensePending = Expense::where('status', 'pending')->count();

        $expensesToday = (float) Expense::where('status', 'approved')
            ->whereDate('approved_at', $today)
            ->sum('amount');

        $labaBersih = $revenueToday - $expensesToday;

        // 7-day revenue chart
        $chartData = collect(range(6, 0))->map(function (int $daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);
            return [
                'label'   => $date->translatedFormat('D'),
                'revenue' => (float) Order::where('status', 'selesai')
                    ->whereDate('created_at', $date)
                    ->sum('total'),
            ];
        })->all();

        return Inertia::render('Owner/Dashboard', [
            'stats' => [
                'revenue_today'   => $revenueToday,
                'orders_today'    => $ordersToday,
                'expense_pending' => $expensePending,
                'laba_bersih'     => $labaBersih,
            ],
            'chartData' => $chartData,
        ]);
    }
}
