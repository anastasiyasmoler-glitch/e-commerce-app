<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReindexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $roles = $this->attributes->get('auth_user_roles');

        if (! is_string($roles) || trim($roles) === '') {
            return false;
        }

        $roleList = array_values(array_filter(array_map('trim', explode(',', $roles))));

        return in_array('admin', $roleList, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function storeId(): ?int
    {
        $value = $this->validated('store_id');

        return is_numeric($value) ? (int) $value : null;
    }
}
