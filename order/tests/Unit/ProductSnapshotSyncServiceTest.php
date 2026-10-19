<?php

namespace Tests\Unit;

use App\Models\ProductSnapshot;
use App\Services\ProductSnapshotSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSnapshotSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_created_upserts_snapshot(): void
    {
        $service = $this->app->make(ProductSnapshotSyncService::class);

        $service->handle('product.created', [
            'id' => 501,
            'store_id' => 3,
            'name' => 'Desk',
            'sku' => 'DSK-1',
            'price' => 19900,
        ]);

        $this->assertDatabaseHas('product_snapshots', [
            'product_id' => 501,
            'store_id' => 3,
            'name' => 'Desk',
            'sku' => 'DSK-1',
            'price_cents' => 19900,
        ]);
    }

    public function test_product_updated_overwrites_existing_snapshot(): void
    {
        ProductSnapshot::query()->create([
            'product_id' => 9,
            'store_id' => 1,
            'name' => 'Old name',
            'sku' => null,
            'price_cents' => 100,
        ]);

        $service = $this->app->make(ProductSnapshotSyncService::class);

        $service->handle('product.updated', [
            'id' => 9,
            'store_id' => 1,
            'name' => 'New name',
            'sku' => 'X',
            'price' => 250,
        ]);

        $this->assertSame('New name', ProductSnapshot::query()->find(9)?->name);
        $this->assertSame(250, ProductSnapshot::query()->find(9)?->price_cents);
    }
}
