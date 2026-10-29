<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListNotificationsRequest;
use App\Models\NotificationLog;
use App\Services\NotificationHistoryService;
use Illuminate\Http\JsonResponse;

class NotificationHistoryController extends Controller
{
    public function __construct(
        private readonly NotificationHistoryService $history,
    ) {}

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        $data = $this->history->list($request->filters())
            ->map(fn (NotificationLog $log) => $this->present($log))
            ->values();

        return response()->json(['data' => $data]);
    }

    public function show(string $id): JsonResponse
    {
        $log = $this->history->find($id);

        if ($log === null) {
            abort(404);
        }

        return response()->json(['data' => $this->present($log)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(NotificationLog $log): array
    {
        return [
            'id' => (string) $log->getKey(),
            'event' => $log->event,
            'channel' => $log->channel,
            'recipient' => $log->recipient,
            'payload' => $log->payload,
            'status' => $log->status,
            'attempts' => $log->attempts,
            'error_message' => $log->error_message,
            'is_dlq' => $log->is_dlq,
            'sent_at' => $log->sent_at,
            'created_at' => $log->created_at,
        ];
    }
}
