<?php

namespace App\Services\Api;

use App\DTO\Api\ServiceResponse;
use App\Http\Resources\Api\ProductResource;
use App\Repositories\ProductRepository;

class ProductService
{
    public function __construct(private ProductRepository $productRepository)
    {
        //
    }

   public function list($searchWord = null, $paginateNumber = 10): ServiceResponse
   {
        $products = $this->productRepository->get($searchWord)->paginate($paginateNumber);
        
        return ServiceResponse::fromArray([
            'data' => ProductResource::collection($products),
        ]);
    }
}
