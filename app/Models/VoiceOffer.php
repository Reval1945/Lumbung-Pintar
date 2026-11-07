<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceOffer extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'parsed_data' => 'array', 'suggested_alternatives' => 'array',
            'reference_price' => 'decimal:2', 'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
}