<?php

namespace App\Repositories\Contracts;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ChatRepositoryInterface extends BaseRepositoryInterface
{
    public function findOrCreateThread(int $buyerId, int $sellerId, ?int $listingId): ChatThread;
    public function findThreadForUserOrFail(int $userId, int $threadId): ChatThread;
    public function paginateThreads(int $userId, int $perPage = 15): LengthAwarePaginator;
    public function paginateMessages(int $threadId, int $perPage = 30): LengthAwarePaginator;
    public function createMessage(ChatThread $thread, int $senderId, array $data): ChatMessage;
    public function markRead(int $threadId, int $readerId): void;
}