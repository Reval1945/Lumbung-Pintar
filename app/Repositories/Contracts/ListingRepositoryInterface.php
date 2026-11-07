<?php

namespace App\Repositories\Contracts;

use App\Models\Listing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ListingRepositoryInterface extends BaseRepositoryInterface
{
    public function paginatePublished(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function paginateBySeller(int $sellerId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findPublishedOrFail(int $id): Listing;
    public function findOwnedOrFail(int $sellerId, int $id): Listing;
    /** Kunci baris (SELECT ... FOR UPDATE), hasil di-key oleh id. */
    public function lockForUpdate(array $ids): Collection;
    public function chunkActive(int $size, callable $callback): void;
}