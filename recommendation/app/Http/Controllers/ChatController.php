<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use App\Services\RecommendationChatService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function store(ChatRequest $request, RecommendationChatService $chat): JsonResponse
    {
        $result = $chat->chat(
            $request->userId(),
            $request->storeId(),
            $request->message(),
            $request->conversationId(),
            $request->resultLimit(),
        );

        return response()->json($result);
    }
}
