<?php

namespace App\Services\Auth;

use App\Models\User;

class AuthRedirectService
{
    public function redirectRouteForUser(User $user): string
    {
        return match (true) {
            $user->hasRole('owner')   => route('owner.dashboard'),
            $user->hasRole('cashier') => route('cashier.dashboard'),
            $user->hasRole('staff')   => route('staff.dashboard'),
            default                   => route('dashboard'),
        };
    }
}
