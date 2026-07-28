<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delta_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Delta Exchange India');
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->string('base_url')->default('https://api.india.delta.exchange');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_sync_enabled')->default(true);
            $table->timestamp('last_sync_started_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::table('sync_logs', function (Blueprint $table) {
            $table->foreignId('delta_account_id')->nullable()->after('shark_account_id')->constrained('delta_accounts')->nullOnDelete();
        });

        Schema::table('trades', function (Blueprint $table) {
            $table->string('exchange')->nullable()->after('broker');
            $table->string('external_trade_id')->nullable()->after('exchange');
            $table->string('external_order_id')->nullable()->after('external_trade_id');
            $table->json('exchange_payload')->nullable()->after('external_order_id');
            $table->unique(['user_id', 'exchange', 'external_trade_id'], 'trades_user_exchange_trade_unique');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropUnique('trades_user_exchange_trade_unique');
            $table->dropColumn(['exchange', 'external_trade_id', 'external_order_id', 'exchange_payload']);
        });
        Schema::table('sync_logs', fn (Blueprint $table) => $table->dropConstrainedForeignId('delta_account_id'));
        Schema::dropIfExists('delta_accounts');
    }
};
