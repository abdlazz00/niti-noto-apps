<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Repositories\MenuItemRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MenuItemService
{
    public function __construct(private MenuItemRepository $menuItemRepository) {}

    public function getAll(): array
    {
        return $this->menuItemRepository->getAll()->map(fn ($item) => [
            'id'            => $item->id,
            'name'          => $item->name,
            'price'         => $item->price,
            'image'         => $item->image,
            'is_active'     => $item->is_active,
            'category_id'   => $item->category_id,
            'category_name' => $item->category->name,
        ])->values()->all();
    }

    public function create(array $data, ?UploadedFile $image): MenuItem
    {
        $imagePath = $image?->store('menu', 'public');

        return $this->menuItemRepository->create([
            'name'        => $data['name'],
            'category_id' => $data['category_id'],
            'price'       => $data['price'],
            'is_active'   => $data['is_active'] ?? true,
            'image'       => $imagePath,
        ]);
    }

    public function update(MenuItem $menuItem, array $data, ?UploadedFile $image): MenuItem
    {
        $imagePath = $menuItem->image;

        if ($image) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $image->store('menu', 'public');
        }

        return $this->menuItemRepository->update($menuItem, [
            'name'        => $data['name'],
            'category_id' => $data['category_id'],
            'price'       => $data['price'],
            'is_active'   => $data['is_active'] ?? true,
            'image'       => $imagePath,
        ]);
    }

    public function delete(MenuItem $menuItem): void
    {
        if ($menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
        }
        $this->menuItemRepository->delete($menuItem);
    }

    public function toggleActive(MenuItem $menuItem): bool
    {
        $newState = ! $menuItem->is_active;
        $this->menuItemRepository->update($menuItem, ['is_active' => $newState]);
        return $newState;
    }
}
