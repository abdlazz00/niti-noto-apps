<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Table;
use App\Repositories\MenuItemRepository;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private MenuItemRepository $menuItemRepository) {}

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
}
