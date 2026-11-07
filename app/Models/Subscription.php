<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'starts_at' => 'date', 'ends_at' => 'date'];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}