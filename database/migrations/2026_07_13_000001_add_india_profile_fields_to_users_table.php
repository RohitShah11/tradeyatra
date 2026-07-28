<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country', 2)->default('IN')->after('password');
            }

            if (! Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 3)->default('INR')->after('country');
            }

            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('Asia/Kolkata')->after('currency');
            }

            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 10)->default('en')->after('timezone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['locale', 'timezone', 'currency', 'country'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
