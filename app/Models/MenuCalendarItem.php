<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuCalendarItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['qty_per_portion' => 'decimal:4'];
    }

    public function menuCalendar(): BelongsTo { return $this->belongsTo(MenuCalendar::class); }
    public function vegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class); }
}