<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->index()->after('operating_system');
            $table->string('country', 100)->nullable()->after('country_code');
            $table->string('region', 120)->nullable()->index()->after('country');
            $table->string('city', 120)->nullable()->index()->after('region');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'country', 'region', 'city']);
        });
    }
};
