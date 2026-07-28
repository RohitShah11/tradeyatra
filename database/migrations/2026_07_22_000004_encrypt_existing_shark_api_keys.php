<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('shark_accounts')
            ->whereNotNull('api_key')
            ->where('api_key', '!=', '')
            ->orderBy('id')
            ->lazy()
            ->each(function ($account): void {
                try {
                    Crypt::decryptString($account->api_key);
                } catch (Throwable) {
                    DB::table('shark_accounts')->where('id', $account->id)->update([
                        'api_key' => Crypt::encryptString($account->api_key),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Encryption is intentionally not reversed.
    }
};
