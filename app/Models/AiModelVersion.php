<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiModelVersion extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['metrics' => 'array', 'is_active' => 'boolean', 'trained_at' => 'datetime'];
    }

    public function evaluations(): HasMany { return $this->hasMany(AiEvaluation::class); }
}