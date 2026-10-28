<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('restrict');
            $table->decimal('price', 12, 2);                // Precio del producto en la divisa especificada
            $table->timestamps();

            $table->unique(['product_id', 'currency_id']);  // Un precio vigente por combinacion producto/divisa
            $table->index('product_id');
            $table->index('currency_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
