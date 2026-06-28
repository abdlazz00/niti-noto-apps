<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Repositories\ExpenseCategoryRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function __construct(private ExpenseCategoryRepository $repository) {}

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:expense_categories,name']]);
        $this->repository->create($request->name);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:expense_categories,name,' . $expenseCategory->id]]);
        $this->repository->update($expenseCategory, $request->name);
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
