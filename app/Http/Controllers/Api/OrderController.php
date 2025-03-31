<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderRequest;
use App\Services\Api\OrderService;
use App\Traits\ResponseHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderController extends Controller
{
    use ResponseHandler;
    
    public function __construct(private OrderService $orderService)
    {
        //
    }

    /**
     * Get the orders of the authenticated user.
     */
    public function index(Request $request)
    {
        try {

            return $this->paginateResponse(
                $this->orderService->listPaginated($request)
            );

        } catch (HttpException $e) {
            return $this->internalServerError($e);
        }
    }

    /**
     * Place an order.
     */
    public function store(OrderRequest $request)
    {
        try {

            return $this->serviceResponse(
                $this->orderService->createOrder($request->validated())
            );

        } catch (HttpException $e) {
            return $this->internalServerError($e);
        }
    }

    /**
     * Delete an order (only if no payments are associated).
    */
    public function destroy($id)
    {
        try {

            return $this->serviceResponse(
                $this->orderService->deleteOrder($id)
            );

        } catch (HttpException $e) {
            return $this->internalServerError($e);
        }
    }
}
