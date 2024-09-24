<?php

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Product::create([
            'name' => 'Sample Product 1',
            'description' => 'This is a sample product description.',
            'qty' => 100,
            'price' => 50.00,
        ]);

        Product::create([
            'name' => 'Sample Product 2',
            'description' => 'Another sample product description.',
            'qty' =>50,
            'price' => 75.00,
        ]);
    }
}
