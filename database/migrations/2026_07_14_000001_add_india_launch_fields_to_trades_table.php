<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            if (! Schema::hasColumn('trades', 'asset_class')) {
                $table->string('asset_class')->default('Crypto')->after('pair');
            }

            if (! Schema::hasColumn('trades', 'market_segment')) {
                $table->string('market_segment')->default('Futures')->after('asset_class');
            }

            if (! Schema::hasColumn('trades', 'currency')) {
                $table->string('currency', 3)->default('INR')->after('market_segment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            foreach (['currency', 'market_segment', 'asset_class'] as $column) {
                if (Schema::hasColumn('trades', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
