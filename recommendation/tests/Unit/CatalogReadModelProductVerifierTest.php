<?php

namespace Tests\Unit;

use App\Services\CatalogReadModelProductVerifier;
use Tests\TestCase;

class CatalogReadModelProductVerifierTest extends TestCase
{
    public function test_drops_unpublished_and_mismatched_rows(): void
    {
        $verifier = new CatalogReadModelProductVerifier;

        $rows = [
            [
                'product_id' => 1,
                'store_id' => 1,
                'is_published' => true,
                'in_stock' => true,
                'payload' => ['id' => 1, 'store_id' => 1, 'name' => 'Sofa'],
            ],
            [
                'product_id' => 2,
                'store_id' => 1,
                'is_published' => false,
                'in_stock' => true,
                'payload' => ['id' => 2, 'store_id' => 1, 'name' => 'Draft'],
            ],
            [
                'product_id' => 3,
                'store_id' => 1,
                'is_published' => true,
                'in_stock' => false,
                'payload' => ['id' => 3, 'store_id' => 1, 'name' => 'Out'],
            ],
        ];

        $this->assertCount(2, $verifier->verify(1, $rows, false));
        $this->assertCount(1, $verifier->verify(1, $rows, true));
    }
}
