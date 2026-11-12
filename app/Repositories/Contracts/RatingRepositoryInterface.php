<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RatingRepositoryInterface extends BaseRepositoryInterface
{
    public function exists(int $orderId, int $raterId, int $rateeId): bool;
    /** @return array{average: float, count: int} */
    public function summaryFor(int $userId): array;
    public function paginateReceived(int $userId, int $perPage = 15): LengthAwarePaginator;
}