<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function thread(): BelongsTo { return $this->belongsTo(ChatThread::class, 'chat_thread_id'); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
}