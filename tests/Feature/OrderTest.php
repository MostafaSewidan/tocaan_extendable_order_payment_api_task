<?php
namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => bcrypt('password')
        ]);
        $this->token = JWTAuth::attempt(['email' => $this->user->email, 'password' => 'password']);
    }

    public function test_list_orders()
    {
        // Create test orders
        $orders = Order::factory()
            ->count(15)
            ->create(['user_id' => $this->user->id,'address' => 'test address'])
            ->each(function ($order) {
                $products = Product::factory()->count(2)->create(['quantity' => 10]);
                $order->products()->attach($products[0]->id, [
                    'quantity' => 2,
                    'price' => $products[0]->price
                ]);
                $order->products()->attach($products[1]->id, [
                    'quantity' => 3,
                    'price' => $products[1]->price
                ]);
                $order->update([
                    'total_price' => ($products[0]->price * 2) + ($products[1]->price * 3)
                ]);
            });

        // Make request to list orders
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/orders');

        // Assert response structure and data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'address',
                        'total_price',
                        'status',
                        'products' => [
                            '*' => [
                                'id',
                                'title',
                                'price',
                                'quantity'
                            ]
                        ]
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(10, 'data'); // Verify we got all 3 orders

        // Test filtering by status
        $confirmedOrder = $orders->first();
        $confirmedOrder->status = OrderStatus::CONFIRMED->value;
        $confirmedOrder->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/orders?status='.OrderStatus::CONFIRMED->value);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // Should only get the confirmed order
            ->assertJsonPath('data.0.status', OrderStatus::CONFIRMED->value);

        // Test pagination
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->getJson('/api/orders?page=2');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_create_order()
    {
        $products = Product::factory()->count(2)->create(['quantity' => 10]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->postJson('/api/orders', [
                    'address' => '123 Test St',
                    'payment_method' => 'sewidan_fake',
                    'products' => [
                        ['id' => $products[0]->id, 'quantity' => 2],
                        ['id' => $products[1]->id, 'quantity' => 3]
                    ]
                ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'payment_url',
                    'order' => [
                        'id',
                        'total_price',
                        'address',
                        'status',
                        'products'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $products[0]->id,
            'quantity' => 8
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $products[1]->id,
            'quantity' => 7
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_price' => ($products[0]->price * 2) + ($products[1]->price * 3)
        ]);
    }

    public function test_delete_order_without_payments()
    {
        $products = Product::factory()->count(2)->create(['quantity' => 10]);
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'address' => '123 Test St',
            'total_price' => ($products[0]->price * 2) + ($products[1]->price * 3)
        ]);

        $order->products()->attach($products[0]->id, ['quantity' => 2, 'price' => $products[0]->price]);
        $order->products()->attach($products[1]->id, ['quantity' => 3, 'price' => $products[1]->price]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}