<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_create_product()
    {
        $response = $this->post('/api/products', [
            'name' => 'Test Product',
            'price' => 50.00,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'name', 'price']);
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 50.00,
        ]);
    }

    public function test_get_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->get('/api/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'price'],
        ]);
    }

    public function test_create_product_validation()
    {
        $response = $this->post('/api/products', [
            'name' => '',
            'price' => '', 
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure(['name', 'price']);
    }

    public function test_get_single_product()
    {
        $product = Product::create([
            'name' => 'Single Product',
            'price' => 100.00,
        ]);

        $response = $this->get("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'name', 'price']);
        $response->assertJson(['name' => 'Single Product']);
    }

    public function test_get_non_existent_product()
    {
        $response = $this->get('/api/products/999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Product not found.']);
    }
}
