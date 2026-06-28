<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\RejectExpenseRequest;
use App\Models\Expense;
use App\Repositories\ExpenseCategoryRepository;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService,
        private ExpenseCategoryRepository $categoryRepository,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'category_id', 'from', 'to']);

        return Inertia::render('Owner/Expense/Index', [
            'expenses'   => $this->expenseService->getAllForOwner($filters),
            'categories' => $this->categoryRepository->getAll()->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all(),
            'filters'    => $filters,
        ]);
    }

    public function approve(Expense $expense): RedirectResponse
    {
        $result = $this->expenseService->approve($expense, auth()->id());

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        return back()->with('success', "Expense \"{$expense->title}\" disetujui.");
    }

    public function reject(RejectExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $result = $this->expenseService->reject($expense, $request->validated('notes'));

        if (is_string($result)) {
            return back()->with('error', $result);
        }

        return back()->with('success', "Expense \"{$expense->title}\" ditolak.");
    }
}
