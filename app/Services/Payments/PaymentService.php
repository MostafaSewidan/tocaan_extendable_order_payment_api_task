<?php

namespace App\Services\Payments;

use App\DTO\Api\ServiceResponse;
use App\DTO\Payment\CallBackResponse;
use App\Enums\OrderPaymentStatus;
use App\Repositories\OrderPaymentRepository;
use App\Services\Api\OrderService;
use App\Services\Payments\Contracts\OrderPaymentInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentService
{
    public function __construct(
        private OrderPaymentRepository $orderPaymentRepository
    )
    {
        //
    }

    /**
     * @param string $paymentType
     * @return OrderPaymentInterface
     * @throws HttpException
     */
    public static function getGateway(string $paymentType): OrderPaymentInterface
    {
        return match ($paymentType) {
            'sewidan_fake' => new SewidanFakePaymentService(),
            default => throw new HttpException(400, 'Invalid payment type'),
        };
    }

    public function updatePayment(Request $request):ServiceResponse
    {
        //the payment type will come from the payment gateway in the real gateway
        $gateWayTransactionData = $this->getGateway('sewidan_fake')
            ->callBack($request);

        $transaction = $this->orderPaymentRepository->findByTransactionId(
            $gateWayTransactionData->gatewayTransactionId
        );

        $status = $gateWayTransactionData->status == CallBackResponse::STATUS_SUCCESS ?
            OrderPaymentStatus::SUCCESSFUL->value : OrderPaymentStatus::FAILED->value;

        $this->orderPaymentRepository->updateTransactionStatus(
            $transaction, 
            $status
        );
       
        return OrderService::updateOrderStatus($transaction);
    }
}
