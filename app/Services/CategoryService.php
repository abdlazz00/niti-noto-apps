<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(private CategoryRepository $categoryRepository) {}

    public function getAll(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        return $this->categoryRepository->update($category, $data);
    }

    public function delete(Category $category): bool
    {
        if ($this->categoryRepository->hasMenuItems($category)) {
            return false;
        }
        $this->categoryRepository->delete($category);
        return true;
    }
}
