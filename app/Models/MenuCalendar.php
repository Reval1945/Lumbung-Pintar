<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCalendar extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['menu_date' => 'date'];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function items(): HasMany { return $this->hasMany(MenuCalendarItem::class); }
}