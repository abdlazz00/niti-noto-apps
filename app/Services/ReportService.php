<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Shift;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function revenueByPeriod(Carbon $from, Carbon $to): array
    {
        $revenue = (float) Order::where('status', 'selesai')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('total');

        $expenses = (float) Expense::where('status', 'approved')
            ->whereBetween('approved_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('amount');

        return [
            'revenue'     => $revenue,
            'expenses'    => $expenses,
            'laba_bersih' => $revenue - $expenses,
        ];
    }

    public function revenueByDay(Carbon $from, Carbon $to): array
    {
        $days    = [];
        $current = $from->copy()->startOfDay();
        $end     = $to->copy()->endOfDay();

        while ($current->lte($end)) {
            $days[] = [
                'date'     => $current->format('d M'),
                'revenue'  => (float) Order::where('status', 'selesai')
                    ->whereDate('created_at', $current)
                    ->sum('total'),
                'expenses' => (float) Expense::where('status', 'approved')
                    ->whereDate('approved_at', $current)
                    ->sum('amount'),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function topMenuItems(Carbon $from, Carbon $to, int $limit = 10): array
    {
        return DB::table('order_items')
            ->join('orders',      'order_items.order_id',    '=', 'orders.id')
            ->join('menu_items',  'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories',  'menu_items.category_id',  '=', 'categories.id')
            ->where('orders.status', 'selesai')
            ->whereBetween('orders.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('
                menu_items.name AS name,
                categories.name AS category,
                SUM(order_items.qty) AS total_qty,
                SUM(order_items.qty * order_items.price) AS total_revenue
            ')
            ->groupBy('menu_items.id', 'menu_items.name', 'categories.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name'          => $r->name,
                'category'      => $r->category,
                'total_qty'     => (int) $r->total_qty,
                'total_revenue' => (float) $r->total_revenue,
            ])
            ->all();
    }

    public function revenueByCategory(Carbon $from, Carbon $to): array
    {
        return DB::table('order_items')
            ->join('orders',     'order_items.order_id',    '=', 'orders.id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories', 'menu_items.category_id',  '=', 'categories.id')
            ->where('orders.status', 'selesai')
            ->whereBetween('orders.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('categories.name AS category, SUM(order_items.qty * order_items.price) AS total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'category' => $r->category,
                'total'    => (float) $r->total,
            ])
            ->all();
    }

    public function shiftSummary(Carbon $from, Carbon $to): array
    {
        return Shift::with('cashier')
            ->whereBetween('started_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderByDesc('started_at')
            ->get()
            ->map(fn ($s) => [
                'cashier'    => $s->cashier?->name ?? '-',
                'started_at' => $s->started_at->format('d M Y H:i'),
                'ended_at'   => $s->ended_at?->format('H:i') ?? 'Aktif',
                'orders'     => $s->orders()->where('status', 'selesai')->count(),
                'revenue'    => (float) $s->total_revenue,
            ])
            ->all();
    }

    public function periodComparison(Carbon $from, Carbon $to): array
    {
        $daysDiff = max(0, $from->diffInDays($to));
        $prevTo   = $from->copy()->subDay();
        $prevFrom = $prevTo->copy()->subDays($daysDiff);

        $current  = $this->revenueByPeriod($from, $to);
        $previous = $this->revenueByPeriod($prevFrom, $prevTo);

        $pct = fn ($curr, $prev) => $prev > 0
            ? round((($curr - $prev) / $prev) * 100, 1)
            : ($curr > 0 ? 100.0 : 0.0);

        return [
            'current'        => $current,
            'previous'       => $previous,
            'prev_period'    => "{$prevFrom->format('d M')} – {$prevTo->format('d M Y')}",
            'revenue_change' => $pct($current['revenue'],     $previous['revenue']),
            'expense_change' => $pct($current['expenses'],    $previous['expenses']),
            'laba_change'    => $pct(max(0, $current['laba_bersih']), max(0, $previous['laba_bersih'])),
        ];
    }
}
