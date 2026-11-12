<?php

namespace App\Repositories\Eloquent;

use App\Models\Rating;
use App\Repositories\Contracts\RatingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RatingRepository extends BaseRepository implements RatingRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Rating());
    }

    public function exists(int $orderId, int $raterId, int $rateeId): bool
    {
        return $this->model->newQuery()
            ->where(['order_id' => $orderId, 'rater_id' => $raterId, 'ratee_id' => $rateeId])
            ->exists();
    }

    public function summaryFor(int $userId): array
    {
        $row = $this->model->newQuery()->where('ratee_id', $userId)
            ->selectRaw('COUNT(*) as total, AVG(score) as average')->first();

        return ['average' => round((float) ($row->average ?? 0), 2), 'count' => (int) ($row->total ?? 0)];
    }

    public function paginateReceived(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->where('ratee_id', $userId)
            ->with('rater:id,name,desa')->latest('id')->paginate($perPage);
    }
}