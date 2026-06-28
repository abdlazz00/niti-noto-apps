<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreTableRequest;
use App\Http\Requests\Owner\UpdateTableRequest;
use App\Models\Table;
use App\Services\TableService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TableController extends Controller
{
    public function __construct(private TableService $tableService) {}

    public function index(): Response
    {
        return Inertia::render('Owner/Table/Index', [
            'tables' => $this->tableService->getAll()->map(fn ($t) => [
                'id'        => $t->id,
                'name'      => $t->name,
                'number'    => $t->number,
                'qr_code'   => $t->qr_code,
                'qr_url'    => url('/order/' . $t->qr_code),
                'is_active' => $t->is_active,
            ])->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owner/Table/Create');
    }

    public function store(StoreTableRequest $request): RedirectResponse
    {
        $this->tableService->create($request->validated());
        return redirect()->route('owner.tables.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit(Table $table): Response
    {
        return Inertia::render('Owner/Table/Edit', ['table' => $table]);
    }

    public function update(UpdateTableRequest $request, Table $table): RedirectResponse
    {
        $this->tableService->update($table, $request->validated());
        return redirect()->route('owner.tables.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(Table $table): RedirectResponse
    {
        $this->tableService->delete($table);
        return redirect()->route('owner.tables.index')->with('success', 'Meja berhasil dihapus.');
    }

    public function toggleActive(Table $table): RedirectResponse
    {
        $newState = $this->tableService->toggleActive($table);
        $label = $newState ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Meja berhasil {$label}.");
    }

    public function qr(Table $table): Response
    {
        return Inertia::render('Owner/Table/Print', [
            'tables' => [[
                'id'     => $table->id,
                'name'   => $table->name,
                'number' => $table->number,
                'qr_url' => url('/order/' . $table->qr_code),
            ]],
            'printAll' => false,
        ]);
    }

    public function printAll(): Response
    {
        $tables = $this->tableService->getAllActive()->map(fn ($t) => [
            'id'     => $t->id,
            'name'   => $t->name,
            'number' => $t->number,
            'qr_url' => url('/order/' . $t->qr_code),
        ])->values()->all();

        return Inertia::render('Owner/Table/Print', [
            'tables'   => $tables,
            'printAll' => true,
        ]);
    }
}
