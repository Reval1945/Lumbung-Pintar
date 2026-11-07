<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandMatch extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'allocated_qty' => 'decimal:2', 'fairness_score' => 'decimal:4',
            'small_farmer_quota_applied' => 'boolean',
        ];
    }

    public function buyerDemand(): BelongsTo { return $this->belongsTo(BuyerDemand::class); }
    public function farmer(): BelongsTo { return $this->belongsTo(User::class, 'farmer_id'); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function plantingCalendar(): BelongsTo { return $this->belongsTo(PlantingCalendar::class); }
    public function modelVersion(): BelongsTo { return $this->belongsTo(AiModelVersion::class, 'ai_model_version_id'); }
}