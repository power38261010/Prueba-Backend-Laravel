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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();               // Nombre de la divisa (ej. Dolar estadounidense)
            $table->string('symbol')->unique();             // Simbolo (ej. USD, $, ARS)
            $table->decimal('exchange_rate', 10, 6)->default(1.000000); // Valor de 1 unidad de la divisa en la divisa base
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
