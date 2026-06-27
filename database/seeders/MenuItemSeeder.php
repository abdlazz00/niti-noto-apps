<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Kopi'     => [
                ['name' => 'Kopi Hitam', 'price' => 8000],
                ['name' => 'Kopi Susu', 'price' => 12000],
                ['name' => 'Cappuccino', 'price' => 18000],
                ['name' => 'Americano', 'price' => 15000],
                ['name' => 'Espresso', 'price' => 12000],
            ],
            'Non-Kopi' => [
                ['name' => 'Teh Manis', 'price' => 5000],
                ['name' => 'Teh Susu', 'price' => 10000],
                ['name' => 'Coklat Panas', 'price' => 15000],
                ['name' => 'Jus Jeruk', 'price' => 12000],
            ],
            'Makanan'  => [
                ['name' => 'Nasi Goreng', 'price' => 20000],
                ['name' => 'Mie Goreng', 'price' => 18000],
                ['name' => 'Roti Bakar', 'price' => 12000],
            ],
            'Snack'    => [
                ['name' => 'Pisang Goreng', 'price' => 8000],
                ['name' => 'Singkong Goreng', 'price' => 8000],
                ['name' => 'Kentang Goreng', 'price' => 12000],
            ],
        ];

        foreach ($items as $categoryName => $menus) {
            $category = Category::where('name', $categoryName)->first();
            if (!$category) continue;

            foreach ($menus as $menu) {
                MenuItem::firstOrCreate(
                    ['name' => $menu['name'], 'category_id' => $category->id],
                    ['price' => $menu['price'], 'is_active' => true]
                );
            }
        }
    }
}
