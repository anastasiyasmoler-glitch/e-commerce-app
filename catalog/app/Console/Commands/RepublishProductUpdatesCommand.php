<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class RepublishProductUpdatesCommand extends Command
{
    protected $signature = 'catalog:republish-product-updates';

    protected $description = 'Emit product.updated for every product (Kafka outbox) to refresh downstream projections';

    public function handle(): int
    {
        $count = 0;

        Product::query()->orderBy('id')->chunkById(100, function ($products) use (&$count): void {
            foreach ($products as $product) {
                $product->touch();
                $count++;
            }
        });

        $this->info("Touched {$count} product(s). Ensure outbox and Recommendation consumer are running.");

        return self::SUCCESS;
    }
}
