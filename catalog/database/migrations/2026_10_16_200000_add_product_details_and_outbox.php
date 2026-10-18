<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(0)->after('sku');
            $table->json('attributes')->nullable()->after('price');
            $table->string('image')->nullable()->after('attributes');
        });

        Schema::create('outbox_messages', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->json('payload');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['published_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_messages');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'attributes', 'image']);
        });
    }
};
