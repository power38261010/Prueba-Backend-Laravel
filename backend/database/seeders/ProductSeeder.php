<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $usd = Currency::where('symbol', 'USD')->firstOrFail();

        $products = [
            [
                'name' => 'Laptop',
                'description' => 'Computadora portatil de 14 pulgadas',
                'price' => 1200.00,
                'currency_id' => $usd->id,
                'tax_cost' => 216.00,
                'manufacturing_cost' => 700.00,
            ],
            [
                'name' => 'Mouse inalambrico',
                'description' => 'Mouse ergonomico sin cable',
                'price' => 25.00,
                'currency_id' => $usd->id,
                'tax_cost' => 4.50,
                'manufacturing_cost' => 10.00,
            ],
            [
                'name' => 'Teclado mecanico',
                'description' => 'Teclado con switches azules',
                'price' => 85.00,
                'currency_id' => $usd->id,
                'tax_cost' => 15.30,
                'manufacturing_cost' => 40.00,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
