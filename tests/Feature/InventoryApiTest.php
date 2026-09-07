<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product()
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Laptop',
            'description' => 'A powerful laptop',
            'price' => 999.99,
            'stock_quantity' => 10,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Laptop');

        $this->assertDatabaseHas('products', ['name' => 'Laptop']);
    }

    public function test_can_update_product_stock()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5
        ]);

        $response = $this->patchJson("/api/products/{$product->id}/stock", [
            'quantity' => 20
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.stock_quantity', 20);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 20
        ]);
    }

    public function test_can_create_order_successfully()
    {
        $product = Product::factory()->create([
            'price' => 100,
            'stock_quantity' => 10
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.customer_name', 'John Doe')
                 ->assertJsonPath('data.total_amount', 200);

        $this->assertDatabaseHas('orders', ['customer_name' => 'John Doe']);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 8]);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 1
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Jane Doe',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5
                ]
            ]
        ]);

        $response->assertStatus(422);
        
        // Stock should remain unchanged
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 1]);
    }

    public function test_can_view_single_order_with_items()
    {
        $product = Product::factory()->create();
        $order = Order::factory()->create();
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $order->id)
                 ->assertJsonCount(1, 'data.items');
    }

    public function test_can_cancel_order_and_restore_stock()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5
        ]);

        $order = Order::factory()->create(['status' => 'pending']);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => $product->price
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 8]);
    }

    public function test_cannot_cancel_already_cancelled_order()
    {
        $order = Order::factory()->create(['status' => 'cancelled']);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(409);
    }
}
