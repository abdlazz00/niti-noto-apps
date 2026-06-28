<?php

namespace App\Services;

use App\Events\ExpenseSubmitted;
use App\Models\Expense;
use App\Repositories\ExpenseCategoryRepository;
use App\Repositories\ExpenseRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ExpenseService
{
    public function __construct(
        private ExpenseRepository $expenseRepository,
        private ExpenseCategoryRepository $categoryRepository,
    ) {}

    public function getMyExpenses(int $userId): array
    {
        return $this->expenseRepository->getByUser($userId)
            ->map(fn ($e) => $this->mapExpense($e))
            ->all();
    }

    public function getAllForOwner(array $filters = []): array
    {
        return $this->expenseRepository->getFiltered($filters)
            ->map(fn ($e) => $this->mapExpense($e))
            ->all();
    }

    public function store(array $data, int $userId): Expense
    {
        $attachmentPath = null;

        if (! empty($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $attachmentPath = $data['attachment']->store('expenses', 'public');
        }

        $expense = $this->expenseRepository->create([
            'title'       => $data['title'],
            'amount'      => $data['amount'],
            'category_id' => $data['category_id'],
            'attachment'  => $attachmentPath,
            'status'      => 'pending',
            'created_by'  => $userId,
        ]);

        broadcast(new ExpenseSubmitted($expense));

        return $expense;
    }

    public function approve(Expense $expense, int $approverId): Expense|string
    {
        if ($expense->status !== 'pending') {
            return 'Expense tidak dalam status pending.';
        }

        return $this->expenseRepository->approve($expense, $approverId);
    }

    public function reject(Expense $expense, string $notes): Expense|string
    {
        if ($expense->status !== 'pending') {
            return 'Expense tidak dalam status pending.';
        }

        return $this->expenseRepository->reject($expense, $notes);
    }

    private function mapExpense(Expense $e): array
    {
        return [
            'id'                 => $e->id,
            'title'              => $e->title,
            'amount'             => (float) $e->amount,
            'category_name'      => $e->category?->name,
            'category_id'        => $e->category_id,
            'attachment'         => $e->attachment ? asset('storage/' . $e->attachment) : null,
            'status'             => $e->status,
            'notes'              => $e->notes,
            'submitted_by'       => $e->createdBy?->name,
            'approved_by'        => $e->approvedBy?->name,
            'approved_at'        => $e->approved_at?->format('d M Y H:i'),
            'created_at'         => $e->created_at->format('d M Y'),
        ];
    }
}
