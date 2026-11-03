<?php

namespace App\Console\Commands;

use Database\Seeders\CatalogSeeder;
use Illuminate\Console\Command;

class ResetDemoCatalogCommand extends Command
{
    protected $signature = 'catalog:reset-demo-catalog
                            {--republish : Emit product.updated for all products after seed}';

    protected $description = 'Wipe demo catalog, reset PostgreSQL id sequences to 1, and reseed';

    public function handle(): int
    {
        $this->warn('This deletes all catalog stores, products, and related rows.');

        $this->call('db:seed', [
            '--class' => CatalogSeeder::class,
            '--force' => true,
            '--no-interaction' => true,
        ]);

        $this->info('Demo catalog reseeded with ids starting at 1.');

        if ($this->option('republish')) {
            $this->call('catalog:republish-product-updates');
        } else {
            $this->line('Run catalog:republish-product-updates after clearing Recommendation/Order/Search local copies.');
        }

        return self::SUCCESS;
    }
}
