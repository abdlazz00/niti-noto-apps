<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cashier\StoreExpenseRequest;
use App\Repositories\ExpenseCategoryRepository;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService,
        private ExpenseCategoryRepository $categoryRepository,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Cashier/Expense/Index', [
            'expenses'   => $this->expenseService->getMyExpenses(auth()->id()),
            'categories' => $this->categoryRepository->getAll()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $this->expenseService->store($request->validated(), auth()->id());
        return back()->with('success', 'Expense berhasil diajukan.');
    }
}
