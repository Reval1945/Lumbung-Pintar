<?php

namespace App\Repositories\Eloquent;

use App\Models\CartItem;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new CartItem());
    }

    public function itemsFor(int $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->with(['listing.vegetableType', 'listing.seller:id,name,desa,kecamatan', 'listing.photos'])
            ->orderBy('id')
            ->get();
    }

    public function findItem(int $userId, int $listingId): ?CartItem
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('listing_id', $listingId)
            ->first();
    }

    public function setQuantity(int $userId, int $listingId, float $quantity): CartItem
    {
        return $this->model->newQuery()->updateOrCreate(
            ['user_id' => $userId, 'listing_id' => $listingId],
            ['quantity' => $quantity],
        );
    }

    public function remove(int $userId, int $listingId): void
    {
        $this->model->newQuery()->where('user_id', $userId)->where('listing_id', $listingId)->delete();
    }

    public function clear(int $userId): void
    {
        $this->model->newQuery()->where('user_id', $userId)->delete();
    }
}