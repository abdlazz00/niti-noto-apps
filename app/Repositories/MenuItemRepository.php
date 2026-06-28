<?php

namespace App\Repositories;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection;

class MenuItemRepository
{
    public function getAll(): Collection
    {
        return MenuItem::with('category')->orderBy('name')->get();
    }

    public function create(array $data): MenuItem
    {
        return MenuItem::create($data);
    }

    public function update(MenuItem $menuItem, array $data): MenuItem
    {
        $menuItem->update($data);
        return $menuItem->fresh();
    }

    public function delete(MenuItem $menuItem): void
    {
        $menuItem->delete();
    }
}
