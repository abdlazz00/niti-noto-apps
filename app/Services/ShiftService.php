<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
use App\Repositories\ShiftRepository;

class ShiftService
{
    public function __construct(private ShiftRepository $shiftRepository) {}

    public function start(User $cashier): Shift|string
    {
        if ($this->shiftRepository->findActiveForCashier($cashier->id)) {
            return 'Shift sudah aktif, tutup shift sebelumnya terlebih dahulu.';
        }

        return $this->shiftRepository->create($cashier->id);
    }

    public function end(User $cashier): Shift|string
    {
        $shift = $this->shiftRepository->findActiveForCashier($cashier->id);

        if (! $shift) {
            return 'Tidak ada shift aktif.';
        }

        $totalRevenue = $shift->orders()->where('status', 'selesai')->sum('total');

        return $this->shiftRepository->end($shift, (float) $totalRevenue);
    }

    public function getCurrent(User $cashier): ?Shift
    {
        return $this->shiftRepository->findActiveForCashier($cashier->id);
    }
}
