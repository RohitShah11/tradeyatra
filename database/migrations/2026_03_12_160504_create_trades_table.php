<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('pair');
            $table->string('trade_type'); // Long / Short
            $table->string('strategy')->nullable();
            $table->string('market_condition')->nullable();
            $table->decimal('lot_size',10,2)->nullable();
            $table->decimal('risk_amount',10,2)->nullable();
            $table->decimal('profit_loss',10,2)->nullable();
            $table->string('emotion')->nullable();
            $table->string('screenshot')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
