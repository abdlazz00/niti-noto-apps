<?php

namespace App\Repositories;

use App\Models\Shift;

class ShiftRepository
{
    public function findActiveForCashier(int $cashierId): ?Shift
    {
        return Shift::where('cashier_id', $cashierId)
            ->whereNull('ended_at')
            ->first();
    }

    public function create(int $cashierId): Shift
    {
        return Shift::create([
            'cashier_id' => $cashierId,
            'started_at' => now(),
        ]);
    }

    public function end(Shift $shift, float $totalRevenue): Shift
    {
        $shift->update([
            'ended_at'      => now(),
            'total_revenue' => $totalRevenue,
        ]);

        return $shift->fresh();
    }
}
