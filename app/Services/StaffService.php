<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\StaffRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffService
{
    public function __construct(private StaffRepository $staffRepository) {}

    public function getAll(): Collection
    {
        return $this->staffRepository->getAllInternal()
            ->map(fn (User $user) => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'photo'     => $user->photo,
                'is_active' => $user->is_active,
                'role'      => $user->roles->first()?->name,
                'phone'     => $user->staffProfile?->phone,
                'join_date' => $user->staffProfile?->join_date,
            ]);
    }

    public function findForEdit(User $user): array
    {
        $user = $this->staffRepository->findWithProfile($user);

        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'photo'     => $user->photo,
            'is_active' => $user->is_active,
            'role'      => $user->roles->first()?->name,
            'phone'     => $user->staffProfile?->phone,
            'address'   => $user->staffProfile?->address,
            'join_date' => $user->staffProfile?->join_date,
            'notes'     => $user->staffProfile?->notes,
        ];
    }

    public function create(array $data, ?UploadedFile $photo): User
    {
        $photoPath = $photo?->store('staff', 'public');

        $user = $this->staffRepository->create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'photo'     => $photoPath,
            'is_active' => true,
        ]);

        $user->assignRole($data['role']);

        $this->staffRepository->createProfile($user->id, $this->extractProfileData($data));

        return $user;
    }

    public function update(User $staff, array $data, ?UploadedFile $photo): User
    {
        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if ($photo) {
            if ($staff->photo) {
                Storage::disk('public')->delete($staff->photo);
            }
            $updateData['photo'] = $photo->store('staff', 'public');
        }

        $staff = $this->staffRepository->update($staff, $updateData);
        $staff->syncRoles([$data['role']]);
        $this->staffRepository->updateOrCreateProfile($staff->id, $this->extractProfileData($data));

        return $staff;
    }

    public function deactivate(User $staff): void
    {
        $this->staffRepository->update($staff, ['is_active' => false]);
    }

    public function toggleActive(User $staff): bool
    {
        $newState = !$staff->is_active;
        $this->staffRepository->update($staff, ['is_active' => $newState]);
        return $newState;
    }

    private function extractProfileData(array $data): array
    {
        return [
            'phone'     => $data['phone'] ?? null,
            'address'   => $data['address'] ?? null,
            'join_date' => $data['join_date'] ?? null,
            'notes'     => $data['notes'] ?? null,
        ];
    }
}
