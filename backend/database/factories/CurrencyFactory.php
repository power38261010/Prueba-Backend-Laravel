<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Dolar estadounidense',
                'Euro',
                'Peso argentino',
                'Real brasileiro',
                'Yen japones',
            ]),
            'symbol' => $this->faker->unique()->randomElement([
                'USD', 'EUR', 'ARS', 'BRL', 'JPY',
            ]),
            'exchange_rate' => $this->faker->randomElement([
                1.000000, 0.900000, 0.001000, 0.180000, 0.006700,
            ]),
        ];
    }
}
