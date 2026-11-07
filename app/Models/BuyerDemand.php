<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuyerDemand extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['needed_date' => 'date', 'quantity' => 'decimal:2', 'max_price' => 'decimal:2'];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function vegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class); }
    public function matches(): HasMany { return $this->hasMany(DemandMatch::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
}