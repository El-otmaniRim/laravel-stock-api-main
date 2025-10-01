<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'productId' => (string) $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'stock' => $this->quantity,  // matching Vue field name
            'unitPrice' => (float) $this->price,
            'total' => (float) $this->price * $this->quantity,
        ];
    }
}
