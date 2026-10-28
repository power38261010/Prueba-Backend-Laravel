<?php

use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de productos y precios por divisa
|--------------------------------------------------------------------------
|
| Endpoints obligatorios del PDF (URI exactas, sin prefijo /api):
|
|   GET    /products
|   POST   /products
|   GET    /products/{id}
|   PUT    /products/{id}
|   DELETE /products/{id}
|   GET    /products/{id}/prices
|   POST   /products/{id}/prices
|
| Se incluye ademas el recurso de divisas ("gestion de la informacion de
| divisas" requerida por la consigna).
*/

Route::controller(ProductController::class)->group(function () {
    Route::get('/products', 'index');
    Route::post('/products', 'store');
    Route::get('/products/{product}', 'show');
    Route::put('/products/{product}', 'update');
    Route::delete('/products/{product}', 'destroy');
});

Route::controller(ProductPriceController::class)->group(function () {
    Route::get('/products/{product}/prices', 'index');
    Route::post('/products/{product}/prices', 'store');
});

Route::controller(CurrencyController::class)->group(function () {
    Route::get('/currencies', 'index');
    Route::post('/currencies', 'store');
    Route::get('/currencies/{currency}', 'show');
    Route::put('/currencies/{currency}', 'update');
    Route::delete('/currencies/{currency}', 'destroy');
});
