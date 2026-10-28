<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\ProductPrice;
use Illuminate\Support\Facades\DB;

class ProductPriceService
{
    public function getProductPrices(int $productId)
    {
        return ProductPrice::with('currency')->where('product_id', $productId)->orderBy('id')->get();
    }

    public function createProductPrice(array $data): ProductPrice
    {
        return DB::transaction(function () use ($data) {
            $this->ensureNoDuplicate($data['product_id'], $data['currency_id']);

            return ProductPrice::create($data);
        });
    }

    private function ensureNoDuplicate(int $productId, int $currencyId): void
    {
        $exists = ProductPrice::where('product_id', $productId)
            ->where('currency_id', $currencyId)
            ->exists();

        if ($exists) {
            throw new BusinessException(
                'Ya existe un precio para este producto en la divisa indicada.',
                422
            );
        }
    }
}
