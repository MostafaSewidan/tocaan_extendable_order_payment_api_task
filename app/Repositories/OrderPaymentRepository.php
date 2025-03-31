<?php

namespace App\Repositories;

use App\Models\{Order,OrderPayment};
use App\Repositories\Contracts\{RepositoryInterface, BaseRepository};
use App\Enums\OrderPaymentStatus;

class OrderPaymentRepository extends BaseRepository implements RepositoryInterface
{
    public function model()
    {
        return new OrderPayment();
    }

    public function createByOrder(Order $order,string $transactionId,string $method)
    {
        return $this->model()->create([
            'order_id' => $order->id,
            'method' => $method,
            'transaction_id' => $transactionId,
            'amount' => $order->total_price,
        ]);
    }

    public function findByTransactionId(string $transactionId)
    {
        return $this->model()
        ->where("transaction_id",$transactionId)
        ->whereStatus(OrderPaymentStatus::PENDING->value)->firstOr(fn() => $this->modelNotFound());
    }

    public function updateTransactionStatus(OrderPayment $orderPayment,string $status)
    {
        $orderPayment->status = $status;
        $orderPayment->save();
        return $orderPayment->refresh();
    }
}
