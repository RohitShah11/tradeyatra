<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->index(['user_id', 'date', 'time'], 'trades_user_date_time_index');
            $table->index(['user_id', 'broker', 'date'], 'trades_user_broker_date_index');
            $table->index(['user_id', 'status', 'date'], 'trades_user_status_date_index');
            $table->index(['user_id', 'imported_at'], 'trades_user_imported_index');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex('trades_user_date_time_index');
            $table->dropIndex('trades_user_broker_date_index');
            $table->dropIndex('trades_user_status_date_index');
            $table->dropIndex('trades_user_imported_index');
        });
    }
};
