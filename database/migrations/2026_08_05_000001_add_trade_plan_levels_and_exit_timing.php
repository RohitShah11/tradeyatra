<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->decimal('planned_stop_loss', 18, 8)->nullable()->after('entry_price');
            $table->decimal('planned_take_profit', 18, 8)->nullable()->after('planned_stop_loss');
            $table->date('exit_date')->nullable()->after('exit_price');
            $table->time('exit_time')->nullable()->after('exit_date');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn([
                'planned_stop_loss',
                'planned_take_profit',
                'exit_date',
                'exit_time',
            ]);
        });
    }
};
