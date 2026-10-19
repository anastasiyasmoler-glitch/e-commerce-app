<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->renameColumn('stripe_payment_intent_id', 'provider_payment_id');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['provider', 'provider_payment_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('payments', 'provider_payment_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex(['provider', 'provider_payment_id']);
            $table->renameColumn('provider_payment_id', 'stripe_payment_intent_id');
        });
    }
};
