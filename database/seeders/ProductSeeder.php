<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductInput;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Milk', 'price' => 2.50, 'initialQuantity' => 100, 'minStockQuantity' => 20, 'unit' => 'l'],
            ['name' => 'Bread', 'price' => 1.80, 'initialQuantity' => 50, 'minStockQuantity' => 10, 'unit' => 'pcs'],
            ['name' => 'Eggs', 'price' => 3.20, 'initialQuantity' => 200, 'minStockQuantity' => 30, 'unit' => 'pcs'],
            ['name' => 'Sugar', 'price' => 1.50, 'initialQuantity' => 80, 'minStockQuantity' => 15, 'unit' => 'kg'],
            ['name' => 'Flour', 'price' => 2.00, 'initialQuantity' => 60, 'minStockQuantity' => 12, 'unit' => 'kg'],
            ['name' => 'Rice', 'price' => 3.50, 'initialQuantity' => 70, 'minStockQuantity' => 15, 'unit' => 'kg'],
            ['name' => 'Pasta', 'price' => 1.90, 'initialQuantity' => 90, 'minStockQuantity' => 20, 'unit' => 'kg'],
            ['name' => 'Oil', 'price' => 4.20, 'initialQuantity' => 40, 'minStockQuantity' => 10, 'unit' => 'l'],
            ['name' => 'Cheese', 'price' => 5.50, 'initialQuantity' => 30, 'minStockQuantity' => 8, 'unit' => 'kg'],
            ['name' => 'Yogurt', 'price' => 1.20, 'initialQuantity' => 120, 'minStockQuantity' => 25, 'unit' => 'pcs'],
            ['name' => 'Butter', 'price' => 3.80, 'initialQuantity' => 45, 'minStockQuantity' => 10, 'unit' => 'kg'],
            ['name' => 'Tomatoes', 'price' => 2.30, 'initialQuantity' => 55, 'minStockQuantity' => 12, 'unit' => 'kg'],
            ['name' => 'Potatoes', 'price' => 1.60, 'initialQuantity' => 95, 'minStockQuantity' => 20, 'unit' => 'kg'],
            ['name' => 'Onions', 'price' => 1.40, 'initialQuantity' => 75, 'minStockQuantity' => 15, 'unit' => 'kg'],
            ['name' => 'Carrots', 'price' => 1.70, 'initialQuantity' => 65, 'minStockQuantity' => 12, 'unit' => 'kg'],
            ['name' => 'Apples', 'price' => 2.80, 'initialQuantity' => 50, 'minStockQuantity' => 10, 'unit' => 'kg'],
            ['name' => 'Bananas', 'price' => 2.10, 'initialQuantity' => 60, 'minStockQuantity' => 12, 'unit' => 'kg'],
            ['name' => 'Coffee', 'price' => 8.50, 'initialQuantity' => 25, 'minStockQuantity' => 5, 'unit' => 'kg'],
            ['name' => 'Tea', 'price' => 3.40, 'initialQuantity' => 35, 'minStockQuantity' => 8, 'unit' => 'pcs'],
            ['name' => 'Salt', 'price' => 0.90, 'initialQuantity' => 110, 'minStockQuantity' => 20, 'unit' => 'kg'],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'name' => $productData['name'],
                'price' => $productData['price'],
                'minStockQuantity' => $productData['minStockQuantity'],
                'unit' => $productData['unit'],
                'updatedAt' => now(),
            ]);

            // Create initial product input
            ProductInput::create([
                'productId' => $product->id,
                'addedQuantity' => $productData['initialQuantity'],
                'createdAt' => now(),
            ]);
        }
    }
}
