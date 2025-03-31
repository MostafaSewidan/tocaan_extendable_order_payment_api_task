<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => number_format($this->total_price, 2),
            'address' => $this->address,
            'status' => $this->status,
            'products' => OrderProductResource::collection($this->products),
        ];
    }
}
