<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('device_type', 30)->nullable()->index()->after('campaign');
            $table->string('browser', 50)->nullable()->index()->after('device_type');
            $table->string('operating_system', 50)->nullable()->after('browser');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropColumn(['device_type', 'browser', 'operating_system']);
        });
    }
};
