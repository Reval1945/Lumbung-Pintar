<?php

namespace App\Http\Resources\Petani;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /** Penjual hanya melihat item miliknya sendiri. */
    public function toArray(Request $request): array
    {
        $mine = $this->items->where('seller_id', $request->user()->id)->values();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'cancelled' => $this->isCancelled(),
            'buyer' => $this->whenLoaded('buyer', fn () => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->name,
                'desa' => $this->buyer->desa,
                'kecamatan' => $this->buyer->kecamatan,
            ]),
            'delivery_address' => $this->delivery_address,
            'needed_date' => $this->needed_date?->toDateString(),
            'notes' => $this->notes,
            'my_subtotal' => (float) $mine->sum('subtotal'),
            'items' => $mine->map(fn ($i) => [
                'listing_id' => $i->listing_id,
                'title' => $i->listing?->title,
                'quantity' => (float) $i->quantity,
                'unit' => $i->unit,
                'unit_price' => (float) $i->unit_price,
                'discount_percent' => $i->discount_percent,
                'subtotal' => (float) $i->subtotal,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}