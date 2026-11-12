<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Rating;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\RatingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class RatingService
{
    public function __construct(
        private RatingRepositoryInterface $ratings,
        private OrderRepositoryInterface $orders,
    ) {}

    public function create(User $buyer, int $orderId, array $data): Rating
    {
        $order = $this->orders->findForBuyerOrFail($buyer->id, $orderId);
        $fail = fn (string $field, string $msg) => throw ValidationException::withMessages([$field => [$msg]]);

        if ($order->isCancelled() || $order->status !== OrderStatus::Selesai) {
            $fail('order', 'Penilaian hanya bisa diberikan setelah pesanan selesai.');
        }
        if (! $order->items->contains('seller_id', (int) $data['seller_id'])) {
            $fail('seller_id', 'Penjual ini tidak ada di pesanan tersebut.');
        }
        if ($this->ratings->exists($order->id, $buyer->id, (int) $data['seller_id'])) {
            $fail('seller_id', 'Anda sudah menilai penjual ini untuk pesanan ini.');
        }

        return $this->ratings->create([
            'order_id' => $order->id,
            'rater_id' => $buyer->id,
            'ratee_id' => (int) $data['seller_id'],
            'score' => $data['score'],
            'quality_score' => $data['quality_score'] ?? null,
            'punctuality_score' => $data['punctuality_score'] ?? null,
            'comment' => $data['comment'] ?? null,
        ]);
    }

    public function summary(int $userId): array
    {
        return $this->ratings->summaryFor($userId);
    }

    public function received(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->ratings->paginateReceived($user->id, $perPage);
    }
}