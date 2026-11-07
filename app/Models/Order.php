<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2', 'service_fee_percent' => 'decimal:2',
            'service_fee_amount' => 'decimal:2', 'total' => 'decimal:2',
            'needed_date' => 'date',
            'confirmed_at' => 'datetime', 'processing_at' => 'datetime', 'shipped_at' => 'datetime',
            'completed_at' => 'datetime', 'cancelled_at' => 'datetime',
        ];
    }

    // Pembatalan dicatat lewat cancelled_at (status tetap lima).
    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function buyer(): BelongsTo { return $this->belongsTo(User::class, 'buyer_id'); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function buyerDemand(): BelongsTo { return $this->belongsTo(BuyerDemand::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payment(): HasOne { return $this->hasOne(Payment::class)->latestOfMany(); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function ratings(): HasMany { return $this->hasMany(Rating::class); }
}