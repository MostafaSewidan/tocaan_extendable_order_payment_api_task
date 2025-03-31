<?php

namespace App\Services\Api;

use App\DTO\Api\ServiceResponse;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Http\Resources\Api\OrderResource;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Repositories\{OrderRepository, ProductRepository, OrderPaymentRepository};
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;


class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository,
        private OrderPaymentRepository $orderPaymentRepository,
        private PaymentService $paymentService
    )
    {
        //
    }

    public function createOrder($requestData): ServiceResponse
    {
        DB::beginTransaction();

        $totalPrice = 0;
        $order = $this->orderRepository->create([
            'user_id' => auth('api')->user()->id,
            'address' => $requestData['address'],
        ]);

        foreach ($requestData['products'] as $requestedProduct) {
            $product = $this->productRepository->findById($requestedProduct['id']);

            $this->checkQtyAvailability($product, $requestedProduct['quantity']);
            $this->productRepository->reduceStock($product, $requestedProduct['quantity']);

            $this->orderRepository->addProduct($order, $product, $requestedProduct['quantity']);
            $totalPrice += $product->price * $requestedProduct['quantity'];
        }
        
        $order->total_price = $totalPrice;
        $order->save();

        // Process payment
        $checkoutData = $this->paymentService->getGateway($requestData['payment_method'])
            ->checkout($order);

        $this->orderPaymentRepository->createByOrder(
            $order,
            $checkoutData->gatewayTransactionId,
            $requestData['payment_method']
        );
        
        DB::commit();
        
        return ServiceResponse::fromArray([
            'data' => [
                "payment_url" => $checkoutData->checkoutUrl,
                "order" => OrderResource::make($order)
            ],
        ]);
    }

    public static function updateOrderStatus(OrderPayment $transaction): ServiceResponse
    {
        $order = $transaction->order;

        match ($transaction->status) {
            OrderPaymentStatus::SUCCESSFUL->value => $order->status = OrderStatus::CONFIRMED,
            OrderPaymentStatus::FAILED->value => $order->status = OrderStatus::CANCELED,
            default => $order->status = 'pending'
        };

        $order->save();

        //return qty back to every product
        if($order->status == OrderStatus::CANCELED){
            $productRepository = new ProductRepository;
            foreach ($order->products as $product) {
                $productRepository->increaseStock($product, $product->pivot->quantity);
            }
        }

        return ServiceResponse::fromArray([
            'data' => OrderResource::make($order),
        ]);
    }

    public function listPaginated(Request $request): ServiceResponse
    {
        $orders = $this->orderRepository->getByUser(
            auth('api')->user(),
            $request->input('status',null),
            ['products']
        )->paginate($request->input('paginate',10));

        return ServiceResponse::fromArray([
            'data' => OrderResource::collection($orders),
        ]);
    }

    public function deleteOrder($id): ServiceResponse
    {
        $order = $this->orderRepository->findUserById(auth('api')->user(),$id);
        
        if ($order->payment)
            return ServiceResponse::fromError([
                'message' => "Cannot delete order with associated payments."
            ]);
            
        $this->orderRepository->delete($id);

        return ServiceResponse::fromArray([
            'message' => 'Order deleted successfully.'
        ]);
    }

    private function checkQtyAvailability(Product $product,int $quantity): bool
    {
        if ($product->quantity < $quantity)
            throw new HttpException(400, "Not enough stock available for product: {$product->title}");

        return true;
    }
}
