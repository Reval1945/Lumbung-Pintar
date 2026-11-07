<?php

namespace App\Repositories\Contracts;

use App\Models\VegetableType;
use Illuminate\Database\Eloquent\Collection;

interface VegetableTypeRepositoryInterface extends BaseRepositoryInterface
{
    public function activeWithTiers(): Collection;
    public function syncTiers(VegetableType $type, array $tiers): void;
}