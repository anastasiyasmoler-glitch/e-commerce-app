<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('store_id');
            $table->timestamp('expires_at');
            $table->timestamp('last_activity_at');
            $table->timestamps();

            $table->index(['user_id', 'store_id']);
        });

        Schema::create('recommendation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('conversation_id')
                ->constrained('recommendation_conversations')
                ->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_messages');
        Schema::dropIfExists('recommendation_conversations');
    }
};
