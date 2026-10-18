<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outbox_messages', function (Blueprint $table) {
            $table->string('locked_by')->nullable()->after('published_at');
            $table->timestamp('locked_until')->nullable()->after('locked_by');

            $table->index('locked_until');
        });
    }

    public function down(): void
    {
        Schema::table('outbox_messages', function (Blueprint $table) {
            $table->dropIndex(['locked_until']);
            $table->dropColumn(['locked_by', 'locked_until']);
        });
    }
};
