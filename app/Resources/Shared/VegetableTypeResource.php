<?php

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VegetableTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'default_unit' => $this->default_unit,
            'fresh_days' => $this->fresh_days,
            'is_active' => $this->is_active,
            'tiers' => $this->whenLoaded('discountTiers', fn () => $this->discountTiers->map(fn ($t) => [
                'days_after_fresh' => $t->days_after_fresh,
                'discount_percent' => $t->discount_percent,
                'label' => $t->label,
            ])),
        ];
    }
}