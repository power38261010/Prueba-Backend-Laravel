<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'Dolar estadounidense',
                'symbol' => 'USD',
                'exchange_rate' => 1.000000, // Divisa base de referencia: 1 USD = 1 USD
            ],
            [
                'name' => 'Euro',
                'symbol' => 'EUR',
                'exchange_rate' => 0.900000, // 1 EUR = 0.90 USD (segun la convencion documentada)
            ],
            [
                'name' => 'Peso argentino',
                'symbol' => 'ARS',
                'exchange_rate' => 0.001000, // 1 ARS = 0.0010 USD
            ],
            [
                'name' => 'Real brasileiro',
                'symbol' => 'BRL',
                'exchange_rate' => 0.180000, // 1 BRL = 0.18 USD
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['symbol' => $currency['symbol']],
                $currency
            );
        }
    }
}
