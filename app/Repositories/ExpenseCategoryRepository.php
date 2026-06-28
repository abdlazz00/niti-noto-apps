<?php

namespace App\Repositories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Collection;

class ExpenseCategoryRepository
{
    public function getAll(): Collection
    {
        return ExpenseCategory::orderBy('name')->get();
    }

    public function create(string $name): ExpenseCategory
    {
        return ExpenseCategory::create(['name' => $name]);
    }

    public function update(ExpenseCategory $category, string $name): ExpenseCategory
    {
        $category->update(['name' => $name]);
        return $category;
    }

    public function delete(ExpenseCategory $category): void
    {
        $category->delete();
    }

    public function hasExpenses(ExpenseCategory $category): bool
    {
        return $category->expenses()->exists();
    }
}
