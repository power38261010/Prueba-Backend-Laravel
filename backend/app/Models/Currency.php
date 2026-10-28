<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    /**
     * Atributes que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'symbol',
        'exchange_rate',
    ];

    /**
     * Casts para mantener precision decimal en importes y tasas de cambio.
     * exchange_rate: valor de 1 unidad de la divisa en la divisa base.
     */
    protected $casts = [
        'exchange_rate' => 'decimal:6',
    ];

    /**
     * Una divisa tiene muchos productos (como su divisa base).
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Una divisa tiene muchos precios de productos registrados en ella.
     */
    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
