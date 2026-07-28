<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 60)->index();
            $table->string('route', 120)->nullable();
            $table->string('path', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('source', 100)->nullable()->index();
            $table->string('medium', 100)->nullable();
            $table->string('campaign', 150)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
