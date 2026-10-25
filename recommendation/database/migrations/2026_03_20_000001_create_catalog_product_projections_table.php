<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('
            CREATE TABLE catalog_product_projections (
                product_id BIGINT NOT NULL,
                store_id BIGINT NOT NULL,
                payload JSONB NOT NULL,
                is_published BOOLEAN NOT NULL DEFAULT false,
                in_stock BOOLEAN NOT NULL DEFAULT true,
                indexed_at TIMESTAMPTZ NULL,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                PRIMARY KEY (product_id, store_id)
            )
        ');

        DB::statement('CREATE INDEX catalog_product_projections_store_id_idx ON catalog_product_projections (store_id)');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::dropIfExists('catalog_product_projections');
    }
};
