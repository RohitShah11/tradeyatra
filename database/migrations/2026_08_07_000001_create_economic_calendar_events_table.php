<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('economic_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 40)->default('FinancialJuice');
            $table->string('external_id', 120);
            $table->text('title');
            $table->string('currency', 12)->nullable()->index();
            $table->string('country', 80)->nullable();
            $table->string('impact', 20)->nullable()->index();
            $table->timestamp('scheduled_at')->index();
            $table->string('actual', 80)->nullable();
            $table->string('forecast', 80)->nullable();
            $table->string('previous', 80)->nullable();
            $table->text('url')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('economic_calendar_events');
    }
};
