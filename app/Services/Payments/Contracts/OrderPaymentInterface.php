<?php

namespace App\Services\Payments\Contracts;

use App\DTO\Payment\{CheckoutResponse, CallBackResponse};
use App\Models\Order;
use Illuminate\Http\Request;

interface OrderPaymentInterface
{
    public function checkout(Order $order):CheckoutResponse;
    public function callBack(Request $request):CallBackResponse;
}
