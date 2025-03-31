<?php
namespace Tests\Feature;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PaymentTest extends TestCase
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

    public function test_payment_callback_success()
    {
        $products = Product::factory()->count(2)->create(['quantity' => 10]);
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'address' => '123 Test St',
            'total_price' => ($products[0]->price * 2) + ($products[1]->price * 3),
            'status' => OrderStatus::PENDING->value
        ]);
        
        $order->products()->attach($products[0]->id, ['quantity' => 2,'price' => $products[0]->price]);
        $order->products()->attach($products[1]->id, ['quantity' => 3,'price' => $products[1]->price]);

        $payment = OrderPayment::factory()->create([
            'order_id' => $order->id,
            'status' => OrderPaymentStatus::PENDING->value,
            'transaction_id' => 'fake_transaction_id',
            'method' => 'sewidan_fake',
            'amount' => $order->total_price,
        ]);
          
        $response = $this->getJson("/api/payment/success?transaction_id=$payment->transaction_id&status=success");

        $response->assertStatus(200)->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'total_price',
                'address',
                'status',
                'products'
            ]
        ])
        ->assertJsonPath('data.status', OrderStatus::CONFIRMED->value)
        ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('order_payments', [
            'id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'status' => OrderPaymentStatus::SUCCESSFUL->value
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CONFIRMED->value
        ]);
    }

    public function test_payment_callback_failure()
    {
        $products = Product::factory()->count(2)->create(['quantity' => 10]);
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'address' => '123 Test St',
            'total_price' => ($products[0]->price * 2) + ($products[1]->price * 3),
            'status' => OrderStatus::PENDING->value
        ]);
        
        $order->products()->attach($products[0]->id, ['quantity' => 2,'price' => $products[0]->price]);
        $order->products()->attach($products[1]->id, ['quantity' => 3,'price' => $products[1]->price]);
        
        $payment = OrderPayment::factory()->create([
            'order_id' => $order->id,
            'status' => OrderPaymentStatus::PENDING->value,
            'transaction_id' => 'fake_transaction_id',
            'method' => 'sewidan_fake',
            'amount' => $order->total_price,
        ]);
          
        $response = $this->getJson("/api/payment/failure?transaction_id=$payment->transaction_id&status=failure");

        $response->assertStatus(200)->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'total_price',
                'address',
                'status',
                'products'
            ]
        ])
        ->assertJsonPath('data.status', OrderStatus::CANCELED->value)
        ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('order_payments', [
            'id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'status' => OrderPaymentStatus::FAILED->value
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $products[0]->id,
            'quantity' => 12
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $products[1]->id,
            'quantity' => 13
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELED->value
        ]);
    }
}