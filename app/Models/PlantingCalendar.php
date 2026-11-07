<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingCalendar extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'plant_date' => 'date', 'expected_harvest_start' => 'date',
            'expected_harvest_end' => 'date', 'actual_harvest_date' => 'date',
            'land_area_m2' => 'decimal:2', 'expected_qty' => 'decimal:2', 'actual_qty' => 'decimal:2',
        ];
    }

    public function farmer(): BelongsTo { return $this->belongsTo(User::class, 'farmer_id'); }
    public function vegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class); }
    public function demandForecast(): BelongsTo { return $this->belongsTo(DemandForecast::class); }
    public function harvestAggregation(): BelongsTo { return $this->belongsTo(HarvestAggregation::class); }
}