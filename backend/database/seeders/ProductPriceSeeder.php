<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Seeder;

class ProductPriceSeeder extends Seeder
{
    public function run(): void
    {
        $productLaptop = Product::where('name', 'Laptop')->firstOrFail();
        $productMouse = Product::where('name', 'Mouse inalambrico')->firstOrFail();

        $usd = Currency::where('symbol', 'USD')->firstOrFail();
        $eur = Currency::where('symbol', 'EUR')->firstOrFail();
        $ars = Currency::where('symbol', 'ARS')->firstOrFail();

        // Precios expresados explicitamente en cada divisa (no se autoconvierten).
        $prices = [
            ['product' => $productLaptop, 'currency' => $usd, 'price' => 1200.00],
            ['product' => $productLaptop, 'currency' => 'EUR', 'price' => 1333.33],
            ['product' => $productLaptop, 'currency' => 'ARS', 'price' => 1200000.00],
            ['product' => $productMouse, 'currency' => 'USD', 'price' => 25.00],
            ['product' => $productMouse, 'currency' => 'EUR', 'price' => 27.78],
            ['product' => $productMouse, 'currency' => 'ARS', 'price' => 25000.00],
        ];

        foreach ($prices as $data) {
            $currency = is_string($data['currency'])
                ? Currency::where('symbol', $data['currency'])->firstOrFail()
                : $data['currency'];

            ProductPrice::updateOrCreate(
                [
                    'product_id' => $data['product']->id,
                    'currency_id' => $currency->id,
                ],
                ['price' => $data['price']]
            );
        }
    }
}
