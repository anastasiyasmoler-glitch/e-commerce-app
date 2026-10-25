<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE catalog_product_projections DROP COLUMN IF EXISTS embedding');
        DB::statement('DROP EXTENSION IF EXISTS vector');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        DB::statement('
            ALTER TABLE catalog_product_projections
            ADD COLUMN IF NOT EXISTS embedding vector('.(int) config('recommendation.embedding_dimensions').')
        ');
    }
};
