<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(private ShiftService $shiftService) {}

    public function start(Request $request): RedirectResponse
    {
        $result = $this->shiftService->start(auth()->user());

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        return back()->with('success', 'Shift berhasil dimulai.');
    }

    public function end(Request $request): RedirectResponse
    {
        $result = $this->shiftService->end(auth()->user());

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        $revenue = 'Rp ' . number_format($result->total_revenue, 0, ',', '.');

        return back()->with('success', "Shift selesai. Total revenue: {$revenue}");
    }
}
