<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Table::firstOrCreate(
                ['number' => $i],
                [
                    'name'      => "Meja $i",
                    'qr_code'   => Str::uuid(),
                    'is_active' => true,
                ]
            );
        }
    }
}
