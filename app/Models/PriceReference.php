<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceReference extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price_date' => 'date', 'price' => 'decimal:2'];
    }

    public function vegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class); }
}