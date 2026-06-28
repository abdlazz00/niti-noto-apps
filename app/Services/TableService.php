<?php

namespace App\Services;

use App\Models\Table;
use App\Repositories\TableRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TableService
{
    public function __construct(private TableRepository $tableRepository) {}

    public function getAll(): Collection
    {
        return $this->tableRepository->getAll();
    }

    public function getAllActive(): Collection
    {
        return $this->tableRepository->getAllActive();
    }

    public function create(array $data): Table
    {
        return $this->tableRepository->create([
            'name'      => $data['name'],
            'number'    => $data['number'],
            'qr_code'   => Str::uuid()->toString(),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(Table $table, array $data): Table
    {
        return $this->tableRepository->update($table, [
            'name'      => $data['name'],
            'number'    => $data['number'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function delete(Table $table): void
    {
        $this->tableRepository->delete($table);
    }

    public function toggleActive(Table $table): bool
    {
        $newState = ! $table->is_active;
        $this->tableRepository->update($table, ['is_active' => $newState]);
        return $newState;
    }
}
