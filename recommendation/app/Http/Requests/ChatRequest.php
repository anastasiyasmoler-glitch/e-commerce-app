<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesGatewayIdentity;
use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    use AuthorizesGatewayIdentity;

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

    public function userId(): int
    {
        return (int) $this->attributes->get('auth_user_id');
    }

    public function storeId(): int
    {
        return (int) $this->validated('store_id');
    }

    public function message(): string
    {
        return (string) $this->validated('message');
    }

    public function conversationId(): ?string
    {
        $id = $this->validated('conversation_id');

        return is_string($id) && $id !== '' ? $id : null;
    }

    public function resultLimit(): int
    {
        $limit = $this->validated('limit');

        if ($limit !== null) {
            return (int) $limit;
        }

        return (int) config('recommendation.chat.default_result_limit', 5);
    }
}
