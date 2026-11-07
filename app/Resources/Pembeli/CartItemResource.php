<?php

namespace App\Http\Resources\Pembeli;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $listing = $this->listing;

        return [
            'listing_id' => $this->listing_id,
            'quantity' => (float) $this->quantity,
            'unit' => $listing->unit,
            'title' => $listing->title,
            'base_price' => (float) $listing->base_price,
            'discount_percent' => $listing->current_discount_percent,
            'price' => $listing->effective_price,
            'line_total' => $this->line_total,
            'is_available' => $this->is_available,
            'freshness_status' => $listing->freshness_status->value,
            'seller' => $listing->seller ? [
                'id' => $listing->seller->id,
                'name' => $listing->seller->name,
                'desa' => $listing->seller->desa,
            ] : null,
        ];
    }
}