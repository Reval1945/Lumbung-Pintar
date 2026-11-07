<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandForecast extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'forecast_date' => 'date', 'predicted_qty' => 'decimal:2',
            'lower_bound' => 'decimal:2', 'upper_bound' => 'decimal:2',
            'actual_qty' => 'decimal:2', 'ape' => 'decimal:4',
        ];
    }

    public function vegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function modelVersion(): BelongsTo { return $this->belongsTo(AiModelVersion::class, 'ai_model_version_id'); }
}