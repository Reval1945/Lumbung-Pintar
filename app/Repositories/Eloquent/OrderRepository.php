<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    private const WITH = ['items.listing:id,title,unit', 'items.seller:id,name', 'payment', 'buyer:id,name,desa,kecamatan'];

    public function __construct()
    {
        parent::__construct(new Order());
    }

    public function paginateForBuyer(int $buyerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('buyer_id', $buyerId)
            ->with(self::WITH)
            ->latest('id')
            ->paginate($perPage);
    }

    public function paginateForSeller(int $sellerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId))
            ->with(self::WITH)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForBuyerOrFail(int $buyerId, int $id): Order
    {
        return $this->model->newQuery()->where('buyer_id', $buyerId)->with(self::WITH)->findOrFail($id);
    }

    public function findForSellerOrFail(int $sellerId, int $id): Order
    {
        return $this->model->newQuery()
            ->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId))
            ->with(self::WITH)
            ->findOrFail($id);
    }

    public function lockForUpdate(int $id): Order
    {
        return $this->model->newQuery()->lockForUpdate()->findOrFail($id);
    }
}