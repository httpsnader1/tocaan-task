<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $prices = [100, 200, 500, 800, 1000];
        $stocks = [10, 50, 100, 200, 500];

        for ($i = 1; $i <= 20; $i++) {

            Product::create([
                'name' => 'Tocaan Task Product ' . $i,
                'price' => $prices[random_int(0, 4)],
                'stock' => $stocks[random_int(0, 4)],
                'is_active' => true,
            ]);

        }
    }
}
