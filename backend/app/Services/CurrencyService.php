<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Currency;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CurrencyService
{
    public function getCurrencies()
    {
        return Currency::orderBy('name')->get();
    }

    public function getCurrency(int $id): Currency
    {
        return Currency::findOrFail($id);
    }

    public function createCurrency(array $data): Currency
    {
        return DB::transaction(function () use ($data) {
            return Currency::create($data);
        });
    }

    public function updateCurrency(Currency $currency, array $data): Currency
    {
        return DB::transaction(function () use ($currency, $data) {
            $currency->fill($data);
            $currency->save();

            return $currency->fresh();
        });
    }

    public function deleteCurrency(Currency $currency): void
    {
        try {
            DB::transaction(function () use ($currency) {
                $currency->delete();
            });
        } catch (QueryException $e) {
            throw new BusinessException(
                'No se puede eliminar la divisa porque esta referenciada por productos o precios.',
                409
            );
        }
    }
}
