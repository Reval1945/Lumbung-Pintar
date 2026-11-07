<?php

namespace App\Http\Resources\Petani;

use App\Http\Resources\Shared\VegetableTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'vegetable_type' => new VegetableTypeResource($this->whenLoaded('vegetableType')),
            'quantity_available' => (float) $this->quantity_available,
            'unit' => $this->unit,
            'min_order_qty' => (float) $this->min_order_qty,
            'base_price' => (float) $this->base_price,
            'effective_price' => $this->effective_price,
            'category' => $this->category,
            'grade' => $this->grade,
            'status' => $this->status->value,
            'freshness_status' => $this->freshness_status->value,
            'discount_percent' => $this->current_discount_percent,
            'harvest_date' => $this->harvest_date?->toDateString(),
            'fresh_until' => $this->fresh_until?->toDateString(),
            'storage_method' => $this->storage_method,
            'confirmed_by_seller_at' => $this->confirmed_by_seller_at,
            'created_via' => $this->created_via,
            'pickup_desa' => $this->pickup_desa,
            'photos' => $this->whenLoaded('photos', fn () => $this->photos->map(fn ($p) => [
                'id' => $p->id,
                'url' => Storage::url($p->path),
                'is_primary' => $p->is_primary,
                'predicted_grade' => $p->predicted_grade,
                'grade_confidence' => $p->grade_confidence !== null ? (float) $p->grade_confidence : null,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}