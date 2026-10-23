<?php

namespace Tests\Unit;

use App\Services\CatalogIndexIngestService;
use Tests\TestCase;

class CatalogIndexIngestServiceTest extends TestCase
{
    public function test_handle_accepts_catalog_payload_without_throwing(): void
    {
        $service = new CatalogIndexIngestService;

        $service->handle('product.created', [
            'id' => 42,
            'store_id' => 1,
            'name' => 'Pink sofa',
        ]);

        $this->assertTrue(true);
    }
}
