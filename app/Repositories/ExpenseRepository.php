<?php

namespace App\Repositories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

class ExpenseRepository
{
    private function baseQuery()
    {
        return Expense::with('category', 'createdBy', 'approvedBy')->latest();
    }

    public function getByUser(int $userId): Collection
    {
        return $this->baseQuery()->where('created_by', $userId)->get();
    }

    public function getFiltered(array $filters): Collection
    {
        $query = $this->baseQuery();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->get();
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function approve(Expense $expense, int $approverId): Expense
    {
        $expense->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $expense->fresh('category', 'approvedBy');
    }

    public function reject(Expense $expense, string $notes): Expense
    {
        $expense->update([
            'status' => 'rejected',
            'notes'  => $notes,
        ]);

        return $expense->fresh();
    }
}
