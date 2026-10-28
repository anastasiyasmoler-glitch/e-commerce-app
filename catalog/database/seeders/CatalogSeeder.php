<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Stock;
use App\Models\Store;
use Database\Seeders\Concerns\SeedsCatalogDemo;
use Database\Seeders\Stores\CozyStorageStoreSeeder;
use Database\Seeders\Stores\NordicHomeStoreSeeder;
use Database\Seeders\Stores\SoftLinenStoreSeeder;
use Database\Seeders\Stores\WarmLightStoreSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CatalogSeeder extends Seeder
{
    use SeedsCatalogDemo;

    /**
     * Seed demo catalog data (stores, categories, products, stocks).
     *
     * The seeder is idempotent: all existing catalog rows are removed
     * in FK-safe order before the demo data is created.
     */
    public function run(): void
    {
        $this->wipeSeedProductImages();

        ProductAttributeValue::query()->delete();
        Stock::query()->delete();
        Product::query()->delete();
        Attribute::query()->delete();
        Category::query()->delete();
        Store::query()->delete();

        $this->call([
            NordicHomeStoreSeeder::class,
            WarmLightStoreSeeder::class,
            SoftLinenStoreSeeder::class,
            CozyStorageStoreSeeder::class,
        ]);

        Cache::forget('catalog:stores');
    }
}
