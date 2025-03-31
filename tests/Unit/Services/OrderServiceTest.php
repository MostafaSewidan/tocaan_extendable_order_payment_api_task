<?php
namespace Tests\Unit\Services;

use App\DTO\Api\ServiceResponse;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderPaymentRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\Api\OrderService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = new OrderService(
            new OrderRepository(),
            new ProductRepository(),
            new OrderPaymentRepository(),
            new PaymentService(new OrderPaymentRepository)
        );
    }

    public function test_create_order_successfully()
    {
        $user = User::factory()->create();
        auth('api')->login($user);
        $products = Product::factory()->count(2)->create(['quantity' => 10]);

        $requestData = [
            'user_id' => $user->id,
            'address' => '123 Test St',
            'products' => [
                ['id' => $products[0]->id, 'quantity' => 2],
                ['id' => $products[1]->id, 'quantity' => 3]
            ],
            'payment_method' => 'sewidan_fake'
        ];
        
        $result = $this->orderService->createOrder($requestData);

        $this->assertInstanceOf(ServiceResponse::class, $result);
        $this->assertEquals(ServiceResponse::STATUS_SUCCESS, $result->status);
        $this->assertArrayHasKey('order', $result->data);
        $this->assertArrayHasKey('payment_url', $result->data);
    }

    public function test_create_order_with_insufficient_stock()
    {
        $user = User::factory()->create();
        auth('api')->login($user);
        $product = Product::factory()->create(['quantity' => 1]);

        $requestData = [
            'user_id' => $user->id,
            'address' => '123 Test St',
            'products' => [
                ['id' => $product->id, 'quantity' => 2]
            ],
            'payment_method' => 'sewidan_fake'
        ];

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->orderService->createOrder($requestData);
    }

    protected function tearDown(): void
    {
        \DB::rollBack();
        parent::tearDown();
    }
}