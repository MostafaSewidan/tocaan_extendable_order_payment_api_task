<?php

namespace App\Repositories\Contracts;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class BaseRepository
{
    public function findById(int $id)
    {
        return $this->model()->findOr($id,fn() => $this->modelNotFound());
    }

    public function create(array $data)
    {
        return $this->model()->create($data)->refresh();
    }
    
    public function update(int $id, array $data)
    {
        $product = $this->findById($id);
        $product->update($data);
        return $product;
    }

    public function delete(int $id)
    {
        return $this->model()->destroy($id);
    }

    public function createMany(array $data)
    {
        return $this->model()->createMany($data);
    }

    public function modelNotFound()
    {
        throw new HttpException(404, 'Model not found');
    }
}
