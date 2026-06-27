<?php

namespace Database\Seeders;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@nitnoto.com'],
            [
                'name'     => 'Owner Niti Noto',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $owner->assignRole('owner');
        StaffProfile::firstOrCreate(['user_id' => $owner->id], ['join_date' => now()]);

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@nitnoto.com'],
            [
                'name'     => 'Kasir Utama',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $cashier->assignRole('cashier');
        StaffProfile::firstOrCreate(['user_id' => $cashier->id], ['join_date' => now()]);

        $staff = User::firstOrCreate(
            ['email' => 'staff@nitnoto.com'],
            [
                'name'     => 'Staff Dapur',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $staff->assignRole('staff');
        StaffProfile::firstOrCreate(['user_id' => $staff->id], ['join_date' => now()]);
    }
}
