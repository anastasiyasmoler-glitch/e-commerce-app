<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesGatewayIdentity;
use Illuminate\Foundation\Http\FormRequest;

class ConversationByIdRequest extends FormRequest
{
    use AuthorizesGatewayIdentity;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    public function userId(): int
    {
        return (int) $this->attributes->get('auth_user_id');
    }

    public function conversationId(): string
    {
        return (string) $this->validated('id');
    }
}
