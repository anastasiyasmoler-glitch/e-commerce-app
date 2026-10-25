<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
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
        $maxMessage = (int) config('recommendation.chat.message_max_length', 2000);
        $maxLimit = (int) config('recommendation.chat.max_result_limit', 20);

        return [
            'message' => ['required', 'string', 'min:1', 'max:'.$maxMessage],
            'store_id' => ['required', 'integer', 'min:1'],
            'conversation_id' => ['nullable', 'uuid'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:'.$maxLimit],
        ];
    }
}
