<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreMenuItemRequest;
use App\Http\Requests\Owner\UpdateMenuItemRequest;
use App\Models\MenuItem;
use App\Repositories\CategoryRepository;
use App\Services\MenuItemService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MenuItemController extends Controller
{
    public function __construct(
        private MenuItemService $menuItemService,
        private CategoryRepository $categoryRepository,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Owner/Menu/Index', [
            'items'      => $this->menuItemService->getAll(),
            'categories' => $this->categoryRepository->getAll(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owner/Menu/Create', [
            'categories' => $this->categoryRepository->getAll(),
        ]);
    }

    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $this->menuItemService->create($request->validated(), $request->file('image'));
        return redirect()->route('owner.menu-items.index')->with('success', 'Menu item berhasil ditambahkan.');
    }

    public function edit(MenuItem $menuItem): Response
    {
        return Inertia::render('Owner/Menu/Edit', [
            'item'       => array_merge($menuItem->toArray(), ['category_name' => $menuItem->category->name]),
            'categories' => $this->categoryRepository->getAll(),
        ]);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $this->menuItemService->update($menuItem, $request->validated(), $request->file('image'));
        return redirect()->route('owner.menu-items.index')->with('success', 'Menu item berhasil diperbarui.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $this->menuItemService->delete($menuItem);
        return redirect()->route('owner.menu-items.index')->with('success', 'Menu item berhasil dihapus.');
    }

    public function toggleActive(MenuItem $menuItem): RedirectResponse
    {
        $newState = $this->menuItemService->toggleActive($menuItem);
        $label = $newState ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Menu item berhasil {$label}.");
    }
}
