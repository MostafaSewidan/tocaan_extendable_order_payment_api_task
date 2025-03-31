<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\BaseRepository;
use App\Repositories\Contracts\RepositoryInterface;

class ProductRepository extends BaseRepository implements RepositoryInterface
{
    public function model()
    {
        return new Product;
    }
    
    public function get($searchWord = null)
    {
        return $this->model()
        ->when(
            $searchWord, 
        fn($q) => $q->where('title', 'like', "%$searchWord%")
        );
    }

    public function reduceStock($product, $quantity)
    {
        $product->decrement('quantity', $quantity);
    }

    public function increaseStock($product, $quantity)
    {
        $product->increment('quantity', $quantity);
    }
}
