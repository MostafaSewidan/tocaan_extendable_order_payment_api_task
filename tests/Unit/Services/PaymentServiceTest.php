<?php
namespace Tests\Unit\Services;

use App\DTO\Payment\CheckoutResponse;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderPaymentRepository;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $paymentService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->paymentService = new PaymentService(
            new OrderPaymentRepository()
        );
    }

    public function test_create_payment_transaction()
    {
        $products = Product::factory()->count(2)->create(['quantity' => 20]);

        foreach(config('services.payment_gateway.active_methods') as $method){
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'address' => '123 Test St',
                'total_price' => ($products[0]->price * 2) + ($products[1]->price * 3),
                'status' => OrderStatus::PENDING->value
            ]);
            
            $order->products()->attach($products[0]->id, ['quantity' => 2,'price' => $products[0]->price]);
            $order->products()->attach($products[1]->id, ['quantity' => 3,'price' => $products[1]->price]);   
            
            $result = $this->paymentService->getGateway($method)
            ->checkout($order);

            $this->assertInstanceOf(CheckoutResponse::class, $result);
            $this->assertEquals(CheckoutResponse::STATUS_SUCCESS, $result->status);
            $this->assertIsString( $result->gatewayTransactionId);
            $this->assertTrue(
                filter_var($result->checkoutUrl, FILTER_VALIDATE_URL) !== false,
                "The <CheckoutResponse>checkoutUrl is not a valid URL"
            );
        }
    }
}