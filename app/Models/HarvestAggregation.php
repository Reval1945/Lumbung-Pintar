<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HarvestAggregation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['harvest_week_start' => 'date', 'total_expected_qty' => 'decimal:2'];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function vegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class); }
    public function plantingCalendars(): HasMany { return $this->hasMany(PlantingCalendar::class); }
}