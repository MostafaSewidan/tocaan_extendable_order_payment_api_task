<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\ProductService;
use App\Traits\ResponseHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductController extends Controller
{
    use ResponseHandler;

    public function __construct(private ProductService $productService)
    {
        //
    }

    /**
     * List all products.
     */
    public function list(Request $request)
    {
        try {
            return $this->paginateResponse(
                $this->productService->list(
                    $request->input('search',null), 
                    $request->input('paginate', 10)
                )
            );
        } catch (HttpException $e) {
            return $this->internalServerError($e);
        }
    }
}
