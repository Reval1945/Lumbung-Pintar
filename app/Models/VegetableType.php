<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VegetableType extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'fresh_days' => 'integer'];
    }

    public function discountTiers(): HasMany { return $this->hasMany(FreshnessDiscountTier::class)->orderBy('days_after_fresh'); }
    public function listings(): HasMany { return $this->hasMany(Listing::class); }
    public function priceReferences(): HasMany { return $this->hasMany(PriceReference::class); }
}