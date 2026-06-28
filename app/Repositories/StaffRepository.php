<?php

namespace App\Repositories;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Collection;

class StaffRepository
{
    public function getAllInternal(): Collection
    {
        return User::with('staffProfile', 'roles')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['owner', 'cashier', 'staff']))
            ->orderBy('name')
            ->get();
    }

    public function findWithProfile(User $user): User
    {
        return $user->load('staffProfile', 'roles');
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function createProfile(int $userId, array $profileData): StaffProfile
    {
        return StaffProfile::create(array_merge(['user_id' => $userId], $profileData));
    }

    public function updateOrCreateProfile(int $userId, array $profileData): StaffProfile
    {
        return StaffProfile::updateOrCreate(
            ['user_id' => $userId],
            $profileData
        );
    }
}
