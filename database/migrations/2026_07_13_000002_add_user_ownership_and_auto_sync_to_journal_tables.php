<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            if (! Schema::hasColumn('trades', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('shark_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('shark_accounts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('shark_accounts', 'auto_sync_enabled')) {
                $table->boolean('auto_sync_enabled')->default(true)->after('is_active');
            }

            if (! Schema::hasColumn('shark_accounts', 'last_sync_started_at')) {
                $table->timestamp('last_sync_started_at')->nullable()->after('auto_sync_enabled');
            }
        });

        Schema::table('sync_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('sync_logs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            if (Schema::hasColumn('sync_logs', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('shark_accounts', function (Blueprint $table) {
            foreach (['last_sync_started_at', 'auto_sync_enabled'] as $column) {
                if (Schema::hasColumn('shark_accounts', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('shark_accounts', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('trades', function (Blueprint $table) {
            if (Schema::hasColumn('trades', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
