<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateQueueStatusRequest;
use App\Models\Order;
use App\Services\StaffQueueService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function __construct(private StaffQueueService $staffQueueService) {}

    public function index(): Response
    {
        $queue = $this->staffQueueService->getQueue();

        return Inertia::render('Staff/Queue', [
            'diterima'    => $queue['diterima'],
            'sedangDibuat' => $queue['sedang_dibuat'],
        ]);
    }

    public function updateStatus(UpdateQueueStatusRequest $request, Order $order): RedirectResponse
    {
        $result = $this->staffQueueService->updateStatus(
            $order,
            $request->validated('status'),
            auth()->id(),
        );

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        return back()->with('success', "Order {$order->order_number} diperbarui.");
    }
}
