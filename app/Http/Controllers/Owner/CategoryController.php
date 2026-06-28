<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreCategoryRequest;
use App\Http\Requests\Owner\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(): Response
    {
        return Inertia::render('Owner/Menu/Categories', [
            'categories' => $this->categoryService->getAll(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->validated());
        return redirect()->route('owner.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->update($category, $request->validated());
        return redirect()->route('owner.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $deleted = $this->categoryService->delete($category);
        if (! $deleted) {
            return redirect()->route('owner.categories.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih memiliki menu item.');
        }
        return redirect()->route('owner.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
