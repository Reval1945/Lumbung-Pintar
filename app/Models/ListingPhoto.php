<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingPhoto extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean', 'is_sellable' => 'boolean',
            'captured_offline' => 'boolean', 'is_training_sample' => 'boolean',
            'type_confidence' => 'decimal:4', 'grade_confidence' => 'decimal:4',
            'synced_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function predictedVegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class, 'predicted_vegetable_type_id'); }
    public function labeledVegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class, 'labeled_vegetable_type_id'); }
    public function modelVersion(): BelongsTo { return $this->belongsTo(AiModelVersion::class, 'ai_model_version_id'); }
    public function labeler(): BelongsTo { return $this->belongsTo(User::class, 'labeled_by'); }
}