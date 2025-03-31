<?php

namespace App\Repositories;

use App\Models\{Order,User,Product};
use App\Repositories\Contracts\{RepositoryInterface, BaseRepository};

class OrderRepository extends BaseRepository implements RepositoryInterface
{
    public function model()
    {
        return new Order;
    }

    public function get(string $status = null)
    {
        return $this->model()
        ->when(
            $status, 
            fn($q) => $q->whereStatus('status', $status)
        );
    }

    public function getByUser(User $user,string $status = null,array $with = [])
    {
        return $user->orders()
        ->when($with, fn($q) => $q->with($with))
        ->when($status, fn($q) => $q->whereStatus($status));
    }

    public function findUserById(User $user,int $id)
    {
        return $user->orders()->findOr($id, fn() => $this->modelNotFound());
    }

    public function addProduct(Order $order,Product $product,int $quantity)
    {
        $order->products()->attach($product->id,
        ['quantity' => $quantity, 'price' => $product->price]
        );
    }
}
