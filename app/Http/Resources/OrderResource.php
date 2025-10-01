<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
 public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'orderNumber' => 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'type' => $this->type,
            'clientId' => $this->user?->id ? (string) $this->user->id : null,
            'clientName' => $this->user?->name,
            'supplierId' => $this->supplier?->id ? (string) $this->supplier->id : null,
            'supplierName' => $this->supplier?->name,
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'subtotal' => $this->items->sum(fn($i) => $i->quantity * $i->price),
            'tax' => 0,
            'total' => (float) $this->total_price,
            'notes' => $this->notes ?? null,
            'deliveryAddress' => $this->delivery_address ?? null,
            'livreurId' => $this->delivery_id ? (string) $this->delivery_id : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
        ];
    }
}
