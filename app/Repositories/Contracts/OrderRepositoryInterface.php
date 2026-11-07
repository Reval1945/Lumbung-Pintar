<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateForBuyer(int $buyerId, int $perPage = 15): LengthAwarePaginator;
    public function paginateForSeller(int $sellerId, int $perPage = 15): LengthAwarePaginator;
    public function findForBuyerOrFail(int $buyerId, int $id): Order;
    public function findForSellerOrFail(int $sellerId, int $id): Order;
    public function lockForUpdate(int $id): Order;
}