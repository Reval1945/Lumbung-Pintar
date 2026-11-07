<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherObservation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'observed_date' => 'date', 'is_forecast' => 'boolean',
            'temp_min' => 'decimal:2', 'temp_max' => 'decimal:2',
            'rainfall_mm' => 'decimal:2', 'humidity' => 'decimal:2',
        ];
    }
}