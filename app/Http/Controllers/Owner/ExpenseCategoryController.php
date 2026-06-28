<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Repositories\ExpenseCategoryRepository;
use Illuminate\Http\RedirectResponse;

class ExpenseCategoryController extends Controller
{
    public function __construct(private ExpenseCategoryRepository $repository) {}

    public function store(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        $this->repository->create($request->validated('name'));
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->repository->update($expenseCategory, $request->validated('name'));
        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($this->repository->hasExpenses($expenseCategory)) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki expense.');
        }
        $this->repository->delete($expenseCategory);
        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
