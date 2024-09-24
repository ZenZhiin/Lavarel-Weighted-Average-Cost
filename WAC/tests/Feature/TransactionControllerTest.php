<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\Models\User::factory()->create([
            'password' => Hash::make('password'),
        ]);
    }

    public function test_create_transaction_with_new_product()
    {
        $response = $this->post('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $token = $response->json('token');

        $response = $this->post('/api/transactions', [
            'product_id' => 0,
            'date' => '2023-09-20',
            'quantity' => 10,
            'type' => 'purchase',
            'price' => 100.50,
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'product_id', 'date', 'quantity', 'type', 'price']);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 100.50,
        ]);

        $this->assertDatabaseHas('transactions', [
            'quantity' => 10,
            'type' => 'purchase',
            'price' => 100.50,
        ]);
    }

    public function test_create_transaction_with_existing_product()
    {
        $product = Product::create([
            'name' => 'Existing Product',
            'price' => 50.00,
        ]);

        $response = $this->post('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $token = $response->json('token');

        $response = $this->post('/api/transactions', [
            'product_id' => $product->id,
            'date' => '2023-09-21',
            'quantity' => 5,
            'type' => 'sale',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'product_id', 'date', 'quantity', 'type', 'price' => null]);

        $this->assertDatabaseHas('transactions', [
            'quantity' => 5,
            'type' => 'sale',
            'product_id' => $product->id,
        ]);
    }

    public function test_invalid_transaction_creation()
    {
        $response = $this->post('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $token = $response->json('token');

        $response = $this->post('/api/transactions', [
            'product_id' => 0,
            'date' => '2023-09-20',
            'quantity' => 10,
            'type' => 'purchase',
        ], ['Authorization' => 'Bearer ' . $token]);

        $response->assertStatus(400);
        $response->assertJsonStructure(['price']);
    }
}
