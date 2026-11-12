<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pembeli\StartChatRequest;
use App\Http\Requests\Shared\SendChatMessageRequest;
use App\Http\Resources\Shared\ChatMessageResource;
use App\Http\Resources\Shared\ChatThreadResource;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function threads(Request $request)
    {
        return ChatThreadResource::collection($this->chat->threads($request->user(), min($request->integer('per_page', 15), 50)));
    }

    public function start(StartChatRequest $request)
    {
        $thread = $this->chat->start($request->user(), $request->validated('seller_id'), $request->validated('listing_id'));

        return (new ChatThreadResource($thread))->response()->setStatusCode(201);
    }

    public function messages(Request $request, int $threadId)
    {
        [$thread, $messages] = $this->chat->messages($request->user(), $threadId, min($request->integer('per_page', 30), 100));

        return ChatMessageResource::collection($messages)
            ->additional(['meta' => ['thread' => new ChatThreadResource($thread)]]);
    }

    public function send(SendChatMessageRequest $request, int $threadId)
    {
        $message = $this->chat->send($request->user(), $threadId, $request->validated('body'));

        return (new ChatMessageResource($message))->response()->setStatusCode(201);
    }
}