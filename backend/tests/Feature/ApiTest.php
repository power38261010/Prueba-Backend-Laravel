<?php

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // DatabaseSeeder: divisas -> productos -> precios
    }

    /** @test */
    public function can_list_products()
    {
        $response = $this->getJson('/products');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data']);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function can_create_product()
    {
        $currency = Currency::first();

        $response = $this->postJson('/products', [
            'name' => 'Monitor',
            'description' => 'Pantalla 27 pulgadas 4K',
            'price' => 350.00,
            'currency_id' => $currency->id,
            'tax_cost' => 63.00,
            'manufacturing_cost' => 200.00,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true, 'message' => 'Producto creado.']);
        $response->assertJsonStructure(['success', 'message', 'data' => [
            'id', 'name', 'description', 'price', 'currency_id', 'currency',
            'tax_cost', 'manufacturing_cost',
        ]]);
        $this->assertEquals('Monitor', $response->json('data.name'));
        $this->assertDatabaseHas('products', ['name' => 'Monitor']);
    }

    /** @test */
    public function can_get_product_by_id()
    {
        $product = Product::first();

        $response = $this->getJson("/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => [
            'id', 'name', 'price', 'currency', 'prices',
        ]]);
        $this->assertEquals($product->id, $response->json('data.id'));
        $this->assertIsArray($response->json('data.currency'));
        $this->assertIsArray($response->json('data.prices'));
    }

    /** @test */
    public function can_update_product_put_semantics_full_update()
    {
        $product = Product::first();
        $currency = Currency::where('symbol', 'EUR')->first();

        $response = $this->putJson("/products/{$product->id}", [
            'name' => 'Laptop actualizado',
            'description' => 'Nueva descripcion',
            'price' => 1500.00,
            'currency_id' => $currency->id,
            'tax_cost' => 270.00,
            'manufacturing_cost' => 900.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Producto actualizado.']);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Laptop actualizado',
            'currency_id' => $currency->id,
        ]);
    }

    /** @test */
    public function can_delete_product()
    {
        $product = Product::first();

        $response = $this->deleteJson("/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function rejects_invalid_product_input()
    {
        $response = $this->postJson('/products', [
            'name' => '',
            'description' => '',
            'price' => -10,
            'currency_id' => 99999,
            'tax_cost' => -5,
            'manufacturing_cost' => -5,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false, 'message' => 'Error de validacion.']);
        $response->assertJsonStructure(['success', 'message', 'errors']);
        $this->assertArrayHasKey('name', $response->json('errors'));
        $this->assertArrayHasKey('price', $response->json('errors'));
        $this->assertArrayHasKey('currency_id', $response->json('errors'));
    }

    /** @test */
    public function returns_404_for_nonexistent_product()
    {
        $this->getJson('/products/99999')->assertStatus(404);
        $this->putJson('/products/99999', [
            'name' => 'X',
            'description' => 'X',
            'price' => 1,
            'currency_id' => Currency::first()->id,
            'tax_cost' => 1,
            'manufacturing_cost' => 1,
        ])->assertStatus(404);
        $this->deleteJson('/products/99999')->assertStatus(404);
    }

    /** @test */
    public function rejects_nonexistent_currency_on_product()
    {
        $response = $this->postJson('/products', [
            'name' => 'Producto',
            'description' => 'Desc',
            'price' => 10,
            'currency_id' => 99999,
            'tax_cost' => 1,
            'manufacturing_cost' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('currency_id', $response->json('errors'));
    }

    /** @test */
    public function can_list_and_create_product_prices()
    {
        $product = Product::where('name', 'Laptop')->first();
        $brl = Currency::where('symbol', 'BRL')->first(); // Laptop no tiene precio en BRL aun

        $response = $this->postJson("/products/{$product->id}/prices", [
            'product_id' => $product->id,
            'currency_id' => $brl->id,
            'price' => 1100.00,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true, 'message' => 'Precio creado.']);
        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $brl->id,
            'price' => 1100.00,
        ]);

        $list = $this->getJson("/products/{$product->id}/prices");
        $list->assertStatus(200);
        $list->assertJson(['success' => true]);
        $this->assertCount(4, $list->json('data')); // USD, EUR, ARS (sembrado) + BRL (nuevo)
    }

    /** @test */
    public function rejects_duplicate_product_price_for_same_currency()
    {
        $product = Product::where('name', 'Laptop')->first();
        $usd = Currency::where('symbol', 'USD')->first();

        // Ya existe precio USD para Laptop (sembrado).
        $response = $this->postJson("/products/{$product->id}/prices", [
            'product_id' => $product->id,
            'currency_id' => $usd->id,
            'price' => 999.00,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('Ya existe un precio', $response->json('message'));
    }

    /** @test */
    public function rejects_inexistent_relationships_on_price()
    {
        // Producto inexistente.
        $this->postJson('/products/99999/prices', [
            'product_id' => 99999,
            'currency_id' => Currency::first()->id,
            'price' => 10,
        ])->assertStatus(404);

        // Divisa inexistente.
        $product = Product::first();
        $this->postJson("/products/{$product->id}/prices", [
            'product_id' => $product->id,
            'currency_id' => 99999,
            'price' => 10,
        ])->assertStatus(422);
    }

    /** @test */
    public function can_manage_currencies()
    {
        // Listar
        $this->getJson('/currencies')->assertStatus(200)
            ->assertJson(['success' => true]);

        // Crear
        $create = $this->postJson('/currencies', [
            'name' => 'Libra esterlina',
            'symbol' => 'GBP',
            'exchange_rate' => 1.250000,
        ]);
        $create->assertStatus(201);
        $currencyId = $create->json('data.id');

        // Consultar
        $this->getJson("/currencies/{$currencyId}")->assertStatus(200)
            ->assertJson(['data' => ['symbol' => 'GBP']]);

        // Actualizar (PUT completo)
        $this->putJson("/currencies/{$currencyId}", [
            'name' => 'Libra esterlina',
            'symbol' => 'GBP',
            'exchange_rate' => 1.300000,
        ])->assertStatus(200)
            ->assertJson(['data' => ['exchange_rate' => '1.300000']]);

        // Eliminar
        $this->deleteJson("/currencies/{$currencyId}")->assertStatus(204);
        $this->assertDatabaseMissing('currencies', ['symbol' => 'GBP']);
    }

    /** @test */
    public function rejects_deleting_currency_referenced_by_products()
    {
        $usd = Currency::where('symbol', 'USD')->first();

        $this->deleteJson("/currencies/{$usd->id}")->assertStatus(409);
    }

    /** @test */
    public function responses_have_consistent_json_envelope_on_errors()
    {
        $response = $this->getJson('/products/0');
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'errors' => null,
        ]);
        $this->assertIsString($response->json('message'));
    }
}
