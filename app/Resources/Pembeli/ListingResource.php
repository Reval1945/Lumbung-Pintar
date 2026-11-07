<?php

namespace App\Http\Resources\Pembeli;

use App\Http\Resources\Shared\VegetableTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ListingResource extends JsonResource
{
    /** Tampilan publik: tanpa kontak penjual (kontak lewat chat aplikasi). */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'vegetable_type' => new VegetableTypeResource($this->whenLoaded('vegetableType')),
            'grade' => $this->grade,
            'category' => $this->category,
            'unit' => $this->unit,
            'quantity_available' => (float) $this->quantity_available,
            'min_order_qty' => (float) $this->min_order_qty,
            'base_price' => (float) $this->base_price,
            'discount_percent' => $this->current_discount_percent,
            'price' => $this->effective_price,
            'freshness_status' => $this->freshness_status->value,
            'harvest_date' => $this->harvest_date?->toDateString(),
            'fresh_until' => $this->fresh_until?->toDateString(),
            'seller' => $this->whenLoaded('seller', fn () => [
                'id' => $this->seller->id,
                'name' => $this->seller->name,
                'desa' => $this->seller->desa,
                'kecamatan' => $this->seller->kecamatan,
            ]),
            'photos' => $this->whenLoaded('photos', fn () => $this->photos->map(fn ($p) => [
                'url' => Storage::url($p->path),
                'is_primary' => $p->is_primary,
            ])),
        ];
    }
}