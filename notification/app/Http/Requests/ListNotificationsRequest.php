<?php

namespace App\Http\Requests;

use App\Data\NotificationHistoryFilters;
use App\Models\NotificationLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'nullable', Rule::in([
                NotificationLog::STATUS_PENDING,
                NotificationLog::STATUS_SENT,
                NotificationLog::STATUS_FAILED,
            ])],
            'channel' => ['sometimes', 'nullable', 'string', 'max:64'],
            'recipient' => ['sometimes', 'nullable', 'string', 'max:255'],
            'from' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    public function filters(): NotificationHistoryFilters
    {
        $validated = $this->validated();

        return new NotificationHistoryFilters(
            status: isset($validated['status']) ? (string) $validated['status'] : null,
            channel: isset($validated['channel']) ? (string) $validated['channel'] : null,
            recipient: isset($validated['recipient']) ? trim((string) $validated['recipient']) : null,
            from: isset($validated['from']) ? (string) $validated['from'] : null,
            to: isset($validated['to']) ? (string) $validated['to'] : null,
        );
    }
}
