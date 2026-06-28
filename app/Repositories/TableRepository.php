<?php

namespace App\Repositories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;

class TableRepository
{
    public function getAll(): Collection
    {
        return Table::orderBy('number')->get();
    }

    public function getAllActive(): Collection
    {
        return Table::active()->orderBy('number')->get();
    }

    public function create(array $data): Table
    {
        return Table::create($data);
    }

    public function update(Table $table, array $data): Table
    {
        $table->update($data);
        return $table->fresh();
    }

    public function delete(Table $table): void
    {
        $table->delete();
    }

    public function numberExists(int $number, ?int $excludeId = null): bool
    {
        return Table::where('number', $number)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
