<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_address' => ['sometimes', 'array'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }

    public function idempotencyKey(): string
    {
        $key = $this->header('Idempotency-Key');

        if (! is_string($key) || trim($key) === '') {
            abort(422, 'Idempotency-Key header is required.');
        }

        return trim($key);
    }

    public function userId(): int
    {
        return (int) $this->attributes->get('auth_user_id');
    }

    public function userEmail(): string
    {
        return (string) $this->attributes->get('auth_user_email');
    }

    /**
     * @return list<array{product_id: int, quantity: int}>
     */
    public function lineItems(): array
    {
        $items = $this->validated('items');

        return array_values(array_map(
            fn (array $item): array => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ],
            $items,
        ));
    }
}
