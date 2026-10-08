<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Services\AuthIdentityClient;
use App\Services\NotificationHistoryService;
use Illuminate\Http\JsonResponse;

class NotificationHistoryController extends Controller
{
    public function __construct(
        private readonly NotificationHistoryService $history,
        private readonly AuthIdentityClient $auth,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorizeStaff();

        $data = $this->history->listLatest()
            ->map(fn (NotificationLog $log) => $this->present($log))
            ->values();

        return response()->json(['data' => $data]);
    }

    public function show(string $id): JsonResponse
    {
        $this->authorizeStaff();

        $log = $this->history->find($id);

        if ($log === null) {
            abort(404);
        }

        return response()->json(['data' => $this->present($log)]);
    }

    private function authorizeStaff(): void
    {
        $roles = $this->auth->roles(request()->bearerToken());

        if ($roles === null) {
            abort(401);
        }

        if (array_intersect($roles, ['admin', 'analyst']) === []) {
            abort(403);
        }
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
