<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_key', 64)->unique();
            $table->string('current_route', 150)->nullable();
            $table->string('current_path', 500)->nullable();
            $table->unsignedInteger('active_seconds')->default(0);
            $table->unsignedInteger('idle_seconds')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('last_seen_at')->index();
            $table->timestamp('last_interacted_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_seen_at']);
        });

        Schema::create('user_page_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_activity_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('route', 150)->nullable();
            $table->string('path', 500);
            $table->unsignedInteger('active_seconds')->default(0);
            $table->unsignedInteger('idle_seconds')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('last_seen_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
            $table->index(['user_activity_session_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_page_sessions');
        Schema::dropIfExists('user_activity_sessions');
    }
};
