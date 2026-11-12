<?php

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'score' => $this->score,
            'quality_score' => $this->quality_score,
            'punctuality_score' => $this->punctuality_score,
            'comment' => $this->comment,
            'rater' => $this->whenLoaded('rater', fn () => ['id' => $this->rater->id, 'name' => $this->rater->name, 'desa' => $this->rater->desa]),
            'created_at' => $this->created_at,
        ];
    }
}