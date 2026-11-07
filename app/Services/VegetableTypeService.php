<?php

namespace App\Services;

use App\Models\VegetableType;
use App\Repositories\Contracts\VegetableTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class VegetableTypeService
{
    public function __construct(private VegetableTypeRepositoryInterface $types) {}

    public function active(): Collection
    {
        return $this->types->activeWithTiers();
    }

    public function create(array $data): VegetableType
    {
        return DB::transaction(function () use ($data) {
            $tiers = Arr::pull($data, 'tiers', []);
            $type = $this->types->create($data);
            $this->types->syncTiers($type, $tiers);

            return $type->load('discountTiers');
        });
    }

    public function update(int $id, array $data): VegetableType
    {
        return DB::transaction(function () use ($id, $data) {
            $hasTiers = array_key_exists('tiers', $data);
            $tiers = Arr::pull($data, 'tiers', []);

            $type = $this->types->update($this->types->findOrFail($id), $data);
            if ($hasTiers) {
                $this->types->syncTiers($type, $tiers);
            }

            return $type->load('discountTiers');
        });
    }
}