<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * El orden respeta las dependencias de claves foraneas:
     * divisas -> productos -> precios por divisa.
     */
    public function run(): void
    {
        $this->call(CurrencySeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(ProductPriceSeeder::class);
    }
}
