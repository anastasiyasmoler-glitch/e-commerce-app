<?php

namespace App\Console\Commands;

use App\Models\Store;
use Database\Seeders\CatalogSeeder;
use Illuminate\Console\Command;

class SeedCatalogDemoIfEmptyCommand extends Command
{
    protected $signature = 'catalog:seed-demo-if-empty';

    protected $description = 'Run CatalogSeeder only when the catalog database has no stores (first boot)';

    public function handle(): int
    {
        if (Store::query()->exists()) {
            $this->line('Catalog already has data; skipping demo seed.');

            return self::SUCCESS;
        }

        $this->call('db:seed', [
            '--class' => CatalogSeeder::class,
            '--force' => true,
            '--no-interaction' => true,
        ]);

        $this->info('Demo catalog seeded.');

        return self::SUCCESS;
    }
}
