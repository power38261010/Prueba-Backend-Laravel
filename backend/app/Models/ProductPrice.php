<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    use HasFactory;

    /**
     * Atributos que se pueden asignar masivamente.
     * price: precio del producto en la divisa indicada por currency_id.
     */
    protected $fillable = [
        'product_id',
        'currency_id',
        'price',
    ];

    /**
     * Cast decimal para preservar precision del importe monetario.
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Producto al que pertenece este precio.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Divisa en la que esta expresado el precio.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
