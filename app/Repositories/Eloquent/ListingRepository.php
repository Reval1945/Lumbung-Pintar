<?php

namespace App\Repositories\Eloquent;

use App\Enums\FreshnessStatus;
use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Repositories\Contracts\ListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListingRepository extends BaseRepository implements ListingRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Listing());
    }

    public function paginatePublished(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->tayang()
            ->where('quantity_available', '>', 0)
            ->where('freshness_status', '!=', FreshnessStatus::TidakLayakJual->value)
            ->with(['vegetableType', 'seller:id,name,desa,kecamatan', 'photos']);

        return $this->applyFilters($query, $filters)->latest('id')->paginate($perPage);
    }

    public function paginateBySeller(int $sellerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->where('seller_id', $sellerId)
            ->with(['vegetableType', 'photos']);

        return $this->applyFilters($query, $filters)->latest('id')->paginate($perPage);
    }

    public function findPublishedOrFail(int $id): Listing
    {
        return $this->model->newQuery()
            ->tayang()
            ->with(['vegetableType', 'seller:id,name,desa,kecamatan', 'photos'])
            ->findOrFail($id);
    }

    public function findOwnedOrFail(int $sellerId, int $id): Listing
    {
        return $this->model->newQuery()
            ->where('seller_id', $sellerId)
            ->with(['vegetableType.discountTiers', 'photos'])
            ->findOrFail($id);
    }

    public function lockForUpdate(array $ids): Collection
    {
        return $this->model->newQuery()
            ->whereIn('id', $ids)
            ->orderBy('id')          // urutan tetap, mencegah deadlock antar transaksi
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    public function chunkActive(int $size, callable $callback): void
    {
        $this->model->newQuery()
            ->with('vegetableType.discountTiers')
            ->where('status', ListingStatus::Tayang->value)
            ->chunkById($size, $callback);
    }

    private function applyFilters(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['vegetable_type_id'] ?? null, fn ($q, $v) => $q->where('vegetable_type_id', $v))
            ->when($f['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($f['grade'] ?? null, fn ($q, $v) => $q->where('grade', $v))
            ->when($f['freshness_status'] ?? null, fn ($q, $v) => $q->where('freshness_status', $v))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"));
    }
}