<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cashier\StorePosOrderRequest;
use App\Models\Table;
use App\Repositories\MenuItemRepository;
use App\Repositories\ShiftRepository;
use App\Services\CashierOrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function __construct(
        private MenuItemRepository $menuItemRepository,
        private ShiftRepository $shiftRepository,
        private CashierOrderService $cashierOrderService,
    ) {}

    public function index(): Response
    {
        $menuItems = $this->menuItemRepository->getAllActive()->map(fn ($item) => [
            'id'            => $item->id,
            'name'          => $item->name,
            'price'         => (float) $item->price,
            'image'         => $item->image ? asset('storage/' . $item->image) : null,
            'category_id'   => $item->category_id,
            'category_name' => $item->category?->name,
        ]);

        $categories = $menuItems->pluck('category_name', 'category_id')->unique()->map(
            fn ($name, $id) => ['id' => $id, 'name' => $name]
        )->values();

        $tables = Table::active()->orderBy('number')->get(['id', 'number', 'name']);

        $shift = $this->shiftRepository->findActiveForCashier(auth()->id());

        return Inertia::render('Cashier/Pos/Index', [
            'menuItems'  => $menuItems->values(),
            'categories' => $categories,
            'tables'     => $tables,
            'shift'      => $shift ? [
                'id'         => $shift->id,
                'started_at' => $shift->started_at->format('H:i'),
            ] : null,
        ]);
    }

    public function store(StorePosOrderRequest $request): RedirectResponse
    {
        $table = Table::findOrFail($request->validated('table_id'));

        $result = $this->cashierOrderService->placeOrder(
            $table,
            $request->validated('items'),
            $request->validated('notes'),
            auth()->id(),
        );

        return back()->with('success', "Order {$result->order_number} berhasil dibuat.");
    }
}
