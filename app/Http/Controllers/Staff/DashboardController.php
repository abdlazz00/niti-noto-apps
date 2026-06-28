<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Staff/Dashboard', [
            'stats' => [
                'active_queue'    => Order::whereIn('status', ['diterima', 'sedang_dibuat'])->count(),
                'completed_today' => Order::where('status', 'selesai')->whereDate('created_at', Carbon::today())->count(),
            ],
        ]);
    }
}
