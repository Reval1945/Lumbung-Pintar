<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEvaluation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['value' => 'decimal:4', 'target' => 'decimal:4', 'evaluated_at' => 'datetime'];
    }

    public function modelVersion(): BelongsTo { return $this->belongsTo(AiModelVersion::class, 'ai_model_version_id'); }
}