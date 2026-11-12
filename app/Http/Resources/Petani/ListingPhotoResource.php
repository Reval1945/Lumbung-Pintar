<?php

namespace App\Http\Resources\Petani;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ListingPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_id' => $this->listing_id,
            'url' => Storage::url($this->path),
            'is_primary' => $this->is_primary,
            'ai' => [
                'status' => $this->ai_status ?? null,   // hanya terisi saat unggah
                'vegetable_type_id' => $this->predicted_vegetable_type_id,
                'type_confidence' => $this->type_confidence !== null ? (float) $this->type_confidence : null,
                'grade' => $this->predicted_grade,
                'grade_confidence' => $this->grade_confidence !== null ? (float) $this->grade_confidence : null,
                'is_sellable' => $this->is_sellable,
            ],
        ];
    }
}