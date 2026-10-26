<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConversationByIdRequest;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ConversationController extends Controller
{
    public function show(ConversationByIdRequest $request, ConversationService $conversations): JsonResponse
    {
        return response()->json(
            $conversations->history($request->userId(), $request->conversationId()),
        );
    }

    public function destroy(ConversationByIdRequest $request, ConversationService $conversations): Response
    {
        $conversations->deleteForUser($request->userId(), $request->conversationId());

        return response()->noContent();
    }
}
