<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreshnessDiscountTier extends Model
{
    protected $guarded = ['id'];

    public function vegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class); }
}