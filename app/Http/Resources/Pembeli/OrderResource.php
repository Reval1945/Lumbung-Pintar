<?php

namespace App\Http\Resources\Pembeli;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'cancelled' => $this->isCancelled(),
            'cancel_reason' => $this->cancel_reason,
            'subtotal' => (float) $this->subtotal,
            'service_fee_percent' => (float) $this->service_fee_percent,
            'service_fee_amount' => (float) $this->service_fee_amount,
            'total' => (float) $this->total,
            'delivery_address' => $this->delivery_address,
            'needed_date' => $this->needed_date?->toDateString(),
            'notes' => $this->notes,
            'timeline' => [
                'confirmed_at' => $this->confirmed_at,
                'processing_at' => $this->processing_at,
                'shipped_at' => $this->shipped_at,
                'completed_at' => $this->completed_at,
                'cancelled_at' => $this->cancelled_at,
            ],
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'listing_id' => $i->listing_id,
                'seller' => $i->seller ? ['id' => $i->seller->id, 'name' => $i->seller->name] : null,
                'title' => $i->listing?->title,
                'quantity' => (float) $i->quantity,
                'unit' => $i->unit,
                'unit_price' => (float) $i->unit_price,
                'discount_percent' => $i->discount_percent,
                'subtotal' => (float) $i->subtotal,
            ])),
            'payment' => $this->whenLoaded('payment', fn () => $this->payment ? [
                'method' => $this->payment->method,
                'status' => $this->payment->status,
                'is_simulated' => $this->payment->is_simulated,
            ] : null),
            'created_at' => $this->created_at,
        ];
    }
}