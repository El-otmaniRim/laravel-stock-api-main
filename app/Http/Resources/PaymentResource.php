<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'paymentMethod' => $this->payment_method,
            'paymentStatus' => $this->payment_status,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
