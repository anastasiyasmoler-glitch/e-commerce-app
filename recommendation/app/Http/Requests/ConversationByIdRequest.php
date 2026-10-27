<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConversationByIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roles = $this->attributes->get('auth_user_roles');

        if (! is_string($roles) || trim($roles) === '') {
            return false;
        }

        $roleList = array_values(array_filter(array_map('trim', explode(',', $roles))));

        return in_array('customer', $roleList, true);
    }

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
