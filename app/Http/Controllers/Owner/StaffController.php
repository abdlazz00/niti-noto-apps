<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(): Response
    {
        $staff = User::with('staffProfile', 'roles')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['owner', 'cashier', 'staff']))
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'photo'       => $user->photo,
                'is_active'   => $user->is_active,
                'role'        => $user->roles->first()?->name,
                'phone'       => $user->staffProfile?->phone,
                'join_date'   => $user->staffProfile?->join_date,
            ]);

        return Inertia::render('Owner/Staff/Index', compact('staff'));
    }

    public function create(): Response
    {
        return Inertia::render('Owner/Staff/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:owner,cashier,staff',
            'photo'    => 'nullable|image|max:2048',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'join_date'=> 'nullable|date',
            'notes'    => 'nullable|string',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('staff', 'public');
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'photo'    => $photoPath,
            'is_active'=> true,
        ]);

        $user->assignRole($data['role']);

        StaffProfile::create([
            'user_id'  => $user->id,
            'phone'    => $data['phone'] ?? null,
            'address'  => $data['address'] ?? null,
            'join_date'=> $data['join_date'] ?? null,
            'notes'    => $data['notes'] ?? null,
        ]);

        return redirect()->route('owner.staff.index')
            ->with('success', 'Staff berhasil ditambahkan.');
    }

    public function edit(User $staff): Response
    {
        $staff->load('staffProfile', 'roles');

        return Inertia::render('Owner/Staff/Edit', [
            'staff' => [
                'id'        => $staff->id,
                'name'      => $staff->name,
                'email'     => $staff->email,
                'photo'     => $staff->photo,
                'is_active' => $staff->is_active,
                'role'      => $staff->roles->first()?->name,
                'phone'     => $staff->staffProfile?->phone,
                'address'   => $staff->staffProfile?->address,
                'join_date' => $staff->staffProfile?->join_date,
                'notes'     => $staff->staffProfile?->notes,
            ],
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $staff->id,
            'password' => 'nullable|string|min:8',
            'role'     => 'required|in:owner,cashier,staff',
            'photo'    => 'nullable|image|max:2048',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'join_date'=> 'nullable|date',
            'notes'    => 'nullable|string',
        ]);

        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('photo')) {
            if ($staff->photo) {
                Storage::disk('public')->delete($staff->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('staff', 'public');
        }

        $staff->update($updateData);

        $staff->syncRoles([$data['role']]);

        $staff->staffProfile()->updateOrCreate(
            ['user_id' => $staff->id],
            [
                'phone'    => $data['phone'] ?? null,
                'address'  => $data['address'] ?? null,
                'join_date'=> $data['join_date'] ?? null,
                'notes'    => $data['notes'] ?? null,
            ]
        );

        return redirect()->route('owner.staff.index')
            ->with('success', 'Data staff berhasil diperbarui.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $staff->update(['is_active' => false]);

        return redirect()->route('owner.staff.index')
            ->with('success', 'Staff berhasil dinonaktifkan.');
    }

    public function toggleActive(User $staff): RedirectResponse
    {
        $staff->update(['is_active' => !$staff->is_active]);

        $msg = $staff->is_active ? 'Staff berhasil diaktifkan.' : 'Staff berhasil dinonaktifkan.';

        return redirect()->route('owner.staff.index')->with('success', $msg);
    }
}
