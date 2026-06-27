<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Bahan Baku', 'Operasional', 'Gaji', 'Lain-lain'];

        foreach ($categories as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }
    }
}
