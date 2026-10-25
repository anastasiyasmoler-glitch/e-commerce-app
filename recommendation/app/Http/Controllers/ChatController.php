<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use App\Services\RecommendationChatService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function store(ChatRequest $request, RecommendationChatService $chat): JsonResponse
    {
        $userId = (int) $request->attributes->get('auth_user_id');
        $storeId = (int) $request->validated('store_id');
        $limit = (int) ($request->validated('limit')
            ?? config('recommendation.chat.default_result_limit', 5));

        $result = $chat->chat(
            $userId,
            $storeId,
            (string) $request->validated('message'),
            $request->validated('conversation_id'),
            $limit,
        );

        return response()->json($result);
    }
}
