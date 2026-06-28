<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreStaffRequest;
use App\Http\Requests\Owner\UpdateStaffRequest;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function __construct(private StaffService $staffService) {}

    public function index(): Response
    {
        return Inertia::render('Owner/Staff/Index', [
            'staff' => $this->staffService->getAll(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owner/Staff/Create');
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $this->staffService->create($request->validated(), $request->file('photo'));

        return redirect()->route('owner.staff.index')
            ->with('success', 'Staff berhasil ditambahkan.');
    }

    public function edit(User $staff): Response
    {
        return Inertia::render('Owner/Staff/Edit', [
            'staff' => $this->staffService->findForEdit($staff),
        ]);
    }

    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $this->staffService->update($staff, $request->validated(), $request->file('photo'));

        return redirect()->route('owner.staff.index')
            ->with('success', 'Data staff berhasil diperbarui.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->staffService->deactivate($staff);

        return redirect()->route('owner.staff.index')
            ->with('success', 'Staff berhasil dinonaktifkan.');
    }

    public function toggleActive(User $staff): RedirectResponse
    {
        $isActive = $this->staffService->toggleActive($staff);
        $msg = $isActive ? 'Staff berhasil diaktifkan.' : 'Staff berhasil dinonaktifkan.';

        return redirect()->route('owner.staff.index')->with('success', $msg);
    }
}
