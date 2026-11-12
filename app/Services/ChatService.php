<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\User;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\ListingRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ChatService
{
    public function __construct(
        private ChatRepositoryInterface $chat,
        private UserRepositoryInterface $users,
        private ListingRepositoryInterface $listings,
    ) {}

    /** Pembeli memulai percakapan dengan penjual (opsional terkait satu tawaran). */
    public function start(User $buyer, int $sellerId, ?int $listingId): ChatThread
    {
        $seller = $this->users->findOrFail($sellerId);

        if (! $seller->hasRole(UserRole::Petani, UserRole::AgenDesa) || ! $seller->is_active) {
            throw ValidationException::withMessages(['seller_id' => ['Penjual tidak ditemukan.']]);
        }

        if ($listingId !== null) {
            $listing = $this->listings->findOrFail($listingId);
            if ($listing->seller_id !== $seller->id) {
                throw ValidationException::withMessages(['listing_id' => ['Tawaran bukan milik penjual ini.']]);
            }
        }

        return $this->chat->findOrCreateThread($buyer->id, $seller->id, $listingId);
    }

    public function threads(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->chat->paginateThreads($user->id, $perPage);
    }

    public function messages(User $user, int $threadId, int $perPage): array
    {
        $thread = $this->chat->findThreadForUserOrFail($user->id, $threadId);
        $this->chat->markRead($thread->id, $user->id);

        return [$thread, $this->chat->paginateMessages($thread->id, $perPage)];
    }

    public function send(User $user, int $threadId, string $body): ChatMessage
    {
        $thread = $this->chat->findThreadForUserOrFail($user->id, $threadId);

        return $this->chat->createMessage($thread, $user->id, [
            'type' => 'teks',
            'body' => $this->maskPhoneNumbers($body),
        ]);
    }

    /** Nomor HP tidak boleh bocor lewat chat (heuristik pola nomor Indonesia). */
    private function maskPhoneNumbers(string $text): string
    {
        return preg_replace('/(?:\+?62|0)[\s\-.]?8[\d\s\-.]{7,12}\d/', '[nomor disembunyikan]', $text);
    }
}