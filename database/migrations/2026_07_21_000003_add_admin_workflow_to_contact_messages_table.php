<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('status', 20)->default('new')->index()->after('message');
            $table->foreignId('handled_by')->nullable()->after('status')->constrained('admins')->nullOnDelete();
            $table->timestamp('handled_at')->nullable()->after('handled_by');
            $table->text('admin_notes')->nullable()->after('handled_at');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('handled_by');
            $table->dropColumn(['status', 'handled_at', 'admin_notes']);
        });
    }
};
