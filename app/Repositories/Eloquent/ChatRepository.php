<?php

namespace App\Repositories\Eloquent;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChatRepository extends BaseRepository implements ChatRepositoryInterface
{
    private const WITH = ['buyer:id,name,desa,kecamatan', 'seller:id,name,desa,kecamatan', 'listing:id,title'];

    public function __construct()
    {
        parent::__construct(new ChatThread());
    }

    public function findOrCreateThread(int $buyerId, int $sellerId, ?int $listingId): ChatThread
    {
        $thread = $this->model->newQuery()->firstOrCreate([
            'buyer_id' => $buyerId, 'seller_id' => $sellerId, 'listing_id' => $listingId,
        ]);

        return $thread->load(self::WITH);
    }

    public function findThreadForUserOrFail(int $userId, int $threadId): ChatThread
    {
        return $this->model->newQuery()
            ->where(fn ($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->with(self::WITH)
            ->findOrFail($threadId);
    }

    public function paginateThreads(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where(fn ($q) => $q->where('buyer_id', $userId)->orWhere('seller_id', $userId))
            ->with(self::WITH)
            ->orderByDesc('last_message_at')->orderByDesc('id')
            ->paginate($perPage);
    }

    public function paginateMessages(int $threadId, int $perPage = 30): LengthAwarePaginator
    {
        return ChatMessage::query()->where('chat_thread_id', $threadId)->latest('id')->paginate($perPage);
    }

    public function createMessage(ChatThread $thread, int $senderId, array $data): ChatMessage
    {
        $message = $thread->messages()->create($data + ['sender_id' => $senderId]);
        $thread->update(['last_message_at' => now()]);

        return $message;
    }

    public function markRead(int $threadId, int $readerId): void
    {
        ChatMessage::query()
            ->where('chat_thread_id', $threadId)
            ->where('sender_id', '!=', $readerId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}