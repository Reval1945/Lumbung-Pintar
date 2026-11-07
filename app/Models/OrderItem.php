<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2', 'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2', 'rejected_qty' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function seller(): BelongsTo { return $this->belongsTo(User::class, 'seller_id'); }
}