<?php

namespace App\Services\Payments;

use App\DTO\Payment\CallBackResponse;
use App\DTO\Payment\CheckoutResponse;
use App\Http\Controllers\SewidanFakeGatewayController;
use App\Models\Order;
use App\Services\Payments\Contracts\OrderPaymentInterface;
use Illuminate\Http\Request;

class SewidanFakePaymentService implements OrderPaymentInterface
{
    public function checkout(Order $order): CheckoutResponse
    {
        $paymentData = $this->get('checkout', [
            'amount' => $order->total_price,
            'user_name' => $order->user?->name,
        ]);
        
        return CheckoutResponse::make(
            checkoutUrl: $paymentData['payment_url'],
            gatewayTransactionId: $paymentData['transaction_id']
        );
    }
    public function callBack(Request $request): CallBackResponse
    {
        $status = $request->input('status') == 'success' ?
            CallBackResponse::STATUS_SUCCESS : CallBackResponse::STATUS_FAILED;

        $transactionId = $request->input('transaction_id');

        return CallBackResponse::make(
            status: $status,
            gatewayTransactionId: $transactionId
        );
    }

    private function request($type, $action, $data)
    {
        $response =  (new SewidanFakeGatewayController)->checkout((new Request([
            'amount' => $data['amount'],
            'user_name' => $data['user_name'],
        ])));

        return json_decode($response->getContent(), true);
    }

    private function post($action, $data = [])
    {
        return $this->request('post', $action, $data);
    }

    private function get($action, $data = [])
    {
        return $this->request('get', $action, $data);
    }
}
