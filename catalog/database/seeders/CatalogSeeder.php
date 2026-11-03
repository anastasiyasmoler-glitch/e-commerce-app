<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\OutboxMessage;
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
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    use SeedsCatalogDemo;

    /**
     * Seed demo catalog data (stores, categories, products, stocks).
     *
     * Destructive: wipes all catalog rows before insert. Run manually only when
     * resetting demo data (php artisan db:seed --class=Database\\Seeders\\CatalogSeeder).
     * Docker entrypoint uses catalog:seed-demo-if-empty (runs once on empty DB).
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
        OutboxMessage::query()->delete();

        $this->resetPostgresIdentitySequences();

        $this->call([
            NordicHomeStoreSeeder::class,
            WarmLightStoreSeeder::class,
            SoftLinenStoreSeeder::class,
            CozyStorageStoreSeeder::class,
        ]);

        Cache::forget('catalog:stores');
    }

    private function resetPostgresIdentitySequences(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ([
            'stores',
            'categories',
            'products',
            'stocks',
            'attributes',
            'product_attribute_values',
            'outbox_messages',
        ] as $table) {
            DB::statement("ALTER SEQUENCE {$table}_id_seq RESTART WITH 1");
        }
    }
}
