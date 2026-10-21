<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();

            $table->unique(['store_id', 'code']);
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);
        });

        if (Schema::hasColumn('products', 'attributes')) {
            $rows = DB::table('products')
                ->select(['id', 'store_id', 'attributes'])
                ->whereNotNull('attributes')
                ->orderBy('id')
                ->get();

            foreach ($rows as $row) {
                $decoded = json_decode((string) $row->attributes, true);
                if (! is_array($decoded)) {
                    continue;
                }

                foreach ($decoded as $code => $value) {
                    if (! is_string($code) || $code === '' || ! is_scalar($value)) {
                        continue;
                    }

                    $attributeId = DB::table('attributes')->where([
                        'store_id' => $row->store_id,
                        'code' => $code,
                    ])->value('id');

                    if ($attributeId === null) {
                        $attributeId = DB::table('attributes')->insertGetId([
                            'store_id' => $row->store_id,
                            'code' => $code,
                            'name' => ucfirst(str_replace('_', ' ', $code)),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('product_attribute_values')->insert([
                        'product_id' => $row->id,
                        'attribute_id' => $attributeId,
                        'value' => (string) $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('attributes');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'attributes')) {
                $table->json('attributes')->nullable()->after('price');
            }
        });

        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attributes');
    }
};
