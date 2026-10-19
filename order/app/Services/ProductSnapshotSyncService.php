<?php

namespace App\Services;

use App\Repositories\Contracts\ProductSnapshotRepositoryInterface;

class ProductSnapshotSyncService
{
    public function __construct(
        private readonly ProductSnapshotRepositoryInterface $snapshots,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $topic, array $payload): void
    {
        if ($topic !== 'product.created' && $topic !== 'product.updated') {
            return;
        }

        $this->snapshots->upsertFromCatalogEvent($payload);
    }
}
