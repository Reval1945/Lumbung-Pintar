<?php

namespace App\Http\Resources\Shared;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $me = $request->user()->id;
        $other = $this->buyer_id === $me ? $this->seller : $this->buyer;

        return [
            'id' => $this->id,
            'listing' => $this->listing ? ['id' => $this->listing->id, 'title' => $this->listing->title] : null,
            'order_id' => $this->order_id,
            // Hanya nama dan wilayah; nomor HP lawan bicara tidak pernah ditampilkan.
            'counterpart' => $other ? ['id' => $other->id, 'name' => $other->name, 'desa' => $other->desa] : null,
            'last_message_at' => $this->last_message_at,
        ];
    }
}