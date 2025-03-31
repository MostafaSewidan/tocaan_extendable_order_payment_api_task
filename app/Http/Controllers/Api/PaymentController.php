<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderRequest;
use App\Services\Api\OrderService;
use App\Services\Payments\PaymentService;
use App\Traits\ResponseHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController extends Controller
{
    use ResponseHandler;
    
    public function __construct(
        private PaymentService $paymentService
    )
    {
        //
    }

    /**
     * Get the orders of the authenticated user.
     */
    public function successPay(Request $request)
    {
        return $this->callBack($request);
    }

    /**
     * Place an order.
     */
    public function failure(Request $request)
    {
        return $this->callBack($request);
    }

    private function callBack($request)
    {
        try {

            return $this->serviceResponse(
                $this->paymentService->updatePayment($request)
            );

        } catch (HttpException $e) {
            return $this->internalServerError($e);
        }
    }
}
