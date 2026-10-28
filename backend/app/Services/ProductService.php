<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getProducts()
    {
        return Product::with('currency')->orderBy('name')->get();
    }

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            return Product::create($data);
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->fill($data);
            $product->save();
            return $product->fresh(['currency', 'prices.currency']);
        });
    }

    public function deleteProduct(Product $product): void
    {
        try {
            DB::transaction(function () use ($product) {
                $product->delete();
            });
        } catch (QueryException $e) {
            throw new \App\Exceptions\BusinessException(
                'No se puede eliminar el producto porque tiene precios asociados.',
                409
            );
        }
    }
}
