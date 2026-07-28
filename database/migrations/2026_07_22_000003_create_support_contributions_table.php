<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contributor_name', 120);
            $table->string('email', 254)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('transaction_reference', 80)->unique();
            $table->text('message')->nullable();
            $table->boolean('show_publicly')->default(false);
            $table->boolean('anonymous')->default(false);
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('verified_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->index();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_contributions');
    }
};
