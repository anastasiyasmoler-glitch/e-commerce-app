<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsCatalogDemo;
use Illuminate\Database\Seeder;

/**
 * Re-download Unsplash photos into MinIO and point existing products at them.
 * Does not wipe catalog rows.
 */
class RefreshProductImagesSeeder extends Seeder
{
    use SeedsCatalogDemo;

    public function run(): void
    {
        $this->refreshDemoProductImages();
    }
}
