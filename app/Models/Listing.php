<?php

namespace App\Models;

use App\Enums\FreshnessStatus;
use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantity_available' => 'decimal:2',
            'min_order_qty' => 'decimal:2',
            'base_price' => 'decimal:2',
            'harvest_date' => 'date',
            'fresh_until' => 'date',
            'confirmed_by_seller_at' => 'datetime',
            'status' => ListingStatus::class,
            'freshness_status' => FreshnessStatus::class,
        ];
    }

    // Harga setelah diskon kesegaran.
    protected function effectivePrice(): Attribute
    {
        return Attribute::get(fn () => round(
            (float) $this->base_price * (1 - $this->current_discount_percent / 100), 2
        ));
    }

    public function scopeTayang(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Tayang->value);
    }

    public function seller(): BelongsTo { return $this->belongsTo(User::class, 'seller_id'); }
    public function vegetableType(): BelongsTo { return $this->belongsTo(VegetableType::class); }
    public function photos(): HasMany { return $this->hasMany(ListingPhoto::class); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
}