<?php

namespace App\Support;

class ProductAttributeMatcher
{
    /**
     * @param  array<string, mixed>  $productAttributes
     * @param  array<string, mixed>  $required
     */
    public static function matches(array $productAttributes, array $required): bool
    {
        if ($required === []) {
            return true;
        }

        foreach ($required as $key => $expected) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            if (! array_key_exists($key, $productAttributes)) {
                return false;
            }

            $actual = $productAttributes[$key];

            if (is_numeric($expected) && is_numeric($actual)) {
                if ((float) $actual !== (float) $expected) {
                    return false;
                }

                continue;
            }

            if ((string) $actual !== (string) $expected) {
                return false;
            }
        }

        return true;
    }
}
