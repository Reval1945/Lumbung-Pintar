<?php

namespace App\Repositories\Eloquent;

use App\Models\VegetableType;
use App\Repositories\Contracts\VegetableTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VegetableTypeRepository extends BaseRepository implements VegetableTypeRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new VegetableType());
    }

    public function activeWithTiers(): Collection
    {
        return $this->model->newQuery()->where('is_active', true)->with('discountTiers')->orderBy('name')->get();
    }

    public function syncTiers(VegetableType $type, array $tiers): void
    {
        $type->discountTiers()->delete();

        if ($tiers !== []) {
            $type->discountTiers()->createMany($tiers);
        }
    }
}