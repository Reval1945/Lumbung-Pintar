<?php

namespace App\Repositories\Contracts;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function itemsFor(int $userId): Collection;
    public function findItem(int $userId, int $listingId): ?CartItem;
    public function setQuantity(int $userId, int $listingId, float $quantity): CartItem;
    public function remove(int $userId, int $listingId): void;
    public function clear(int $userId): void;
}