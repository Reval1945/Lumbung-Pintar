<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupRouteStop extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['planned_arrival' => 'datetime', 'actual_arrival' => 'datetime', 'planned_qty' => 'decimal:2'];
    }

    public function route(): BelongsTo { return $this->belongsTo(PickupRoute::class, 'pickup_route_id'); }
    public function farmer(): BelongsTo { return $this->belongsTo(User::class, 'farmer_id'); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}