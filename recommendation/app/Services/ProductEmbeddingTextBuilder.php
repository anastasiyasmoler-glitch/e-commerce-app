<?php

namespace App\Services;

class ProductEmbeddingTextBuilder
{
    /**
     * Flatten a Catalog product.created / product.updated payload into text for embeddings.
     *
     * @param  array<string, mixed>  $payload
     */
    public function fromCatalogPayload(array $payload): string
    {
        $parts = [
            $this->stringValue($payload['name'] ?? null),
            $this->stringValue($payload['description'] ?? null),
            $this->stringValue($payload['sku'] ?? null),
            $this->labelled('category', $payload['category_id'] ?? null),
            $this->attributes($payload['attributes'] ?? null),
        ];

        $parts = array_values(array_filter($parts, static fn (string $part): bool => $part !== ''));

        return implode("\n", $parts);
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function labelled(string $label, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return $label.': '.(is_scalar($value) ? (string) $value : '');
    }

    private function attributes(mixed $attributes): string
    {
        if (! is_array($attributes) || $attributes === []) {
            return '';
        }

        $chunks = [];
        foreach ($attributes as $name => $value) {
            if (! is_string($name) || $name === '' || ! is_scalar($value)) {
                continue;
            }

            $chunks[] = $name.': '.(string) $value;
        }

        return implode(', ', $chunks);
    }
}
