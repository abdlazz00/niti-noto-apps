<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreOrderRequest;
use App\Models\Category;
use App\Models\Table;
use App\Repositories\MenuItemRepository;
use App\Services\CustomerOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private MenuItemRepository $menuItemRepository,
        private CustomerOrderService $customerOrderService,
    ) {}

    public function menu(string $qrCode): Response
    {
        $table = Table::where('qr_code', $qrCode)->where('is_active', true)->firstOrFail();

        $activeItems = $this->menuItemRepository->getAllActive();

        $categories = Category::whereHas('menuItems', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ->values()
            ->all();

        $items = $activeItems->map(fn ($item) => [
            'id'          => $item->id,
            'name'        => $item->name,
            'price'       => (float) $item->price,
            'image'       => $item->image,
            'category_id' => $item->category_id,
        ])->values()->all();

        return Inertia::render('Customer/Order/Menu', [
            'table'      => ['id' => $table->id, 'number' => $table->number, 'name' => $table->name, 'qr_code' => $table->qr_code],
            'categories' => $categories,
            'items'      => $items,
        ]);
    }

    public function checkout(string $qrCode): Response
    {
        $table = Table::where('qr_code', $qrCode)->where('is_active', true)->firstOrFail();

        return Inertia::render('Customer/Order/Checkout', [
            'table' => ['id' => $table->id, 'number' => $table->number, 'name' => $table->name, 'qr_code' => $table->qr_code],
        ]);
    }

    public function store(StoreOrderRequest $request, string $qrCode): RedirectResponse
    {
        $table = Table::where('qr_code', $qrCode)->where('is_active', true)->firstOrFail();

        $order = $this->customerOrderService->placeOrder(
            $table,
            $request->validated('items'),
            $request->validated('notes'),
        );

        // Signed URL prevents IDOR enumeration — customer can only access their own order
        $trackUrl = URL::signedRoute('order.track', ['order' => $order->id]);

        return redirect($trackUrl)
            ->with('success', 'Pesanan berhasil dibuat! Harap tunggu konfirmasi kasir.');
    }
}
