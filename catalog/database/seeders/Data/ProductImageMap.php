<?php

namespace Database\Seeders\Data;

/**
 * Demo catalog photos live in product-photos/{slug}.jpg (generated product shots).
 */
final class ProductImageMap
{
    public static function pathForSlug(string $slug): ?string
    {
        $path = database_path('seeders/Data/product-photos/'.$slug.'.jpg');

        return is_file($path) ? $path : null;
    }
}
