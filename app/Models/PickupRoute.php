<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickupRoute extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'route_date' => 'date',
            'total_distance_km' => 'decimal:2', 'baseline_distance_km' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function driver(): BelongsTo { return $this->belongsTo(User::class, 'driver_id'); }
    public function stops(): HasMany { return $this->hasMany(PickupRouteStop::class)->orderBy('sequence'); }
    public function modelVersion(): BelongsTo { return $this->belongsTo(AiModelVersion::class, 'ai_model_version_id'); }
}