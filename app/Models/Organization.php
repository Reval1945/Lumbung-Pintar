<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'verified_at' => 'datetime'];
    }

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function demands(): HasMany { return $this->hasMany(BuyerDemand::class); }
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
    public function menuCalendars(): HasMany { return $this->hasMany(MenuCalendar::class); }
}