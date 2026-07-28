<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $this->addColumnIfMissing($table, 'broker', 'string', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'profit', 'decimal', ['total' => 14, 'places' => 4, 'nullable' => true]);
            $this->addColumnIfMissing($table, 'loss', 'decimal', ['total' => 14, 'places' => 4, 'nullable' => true]);
            $this->addColumnIfMissing($table, 'trading_fees', 'decimal', ['total' => 14, 'places' => 4, 'nullable' => true]);
            $this->addColumnIfMissing($table, 'current_balance', 'decimal', ['total' => 14, 'places' => 4, 'nullable' => true]);
            $this->addColumnIfMissing($table, 'entry_price', 'decimal', ['total' => 18, 'places' => 8, 'nullable' => true]);
            $this->addColumnIfMissing($table, 'exit_price', 'decimal', ['total' => 18, 'places' => 8, 'nullable' => true]);
            $this->addColumnIfMissing($table, 'quantity', 'decimal', ['total' => 18, 'places' => 8, 'nullable' => true]);
            $this->addColumnIfMissing($table, 'leverage', 'decimal', ['total' => 10, 'places' => 2, 'nullable' => true]);
            $this->addColumnIfMissing($table, 'status', 'string', ['nullable' => true, 'default' => 'Closed']);
            $this->addColumnIfMissing($table, 'setup_quality', 'unsignedTinyInteger', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'mistake_tags', 'json', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'exit_reason', 'string', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'plan_followed', 'boolean', ['default' => true]);
            $this->addColumnIfMissing($table, 'shark_order_id', 'string', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'shark_trade_id', 'string', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'shark_position_id', 'string', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'shark_payload', 'json', ['nullable' => true]);
            $this->addColumnIfMissing($table, 'imported_at', 'timestamp', ['nullable' => true]);
        });

        Schema::create('shark_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('SharkExchange');
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->string('base_url')->default('https://api.sharkexchange.in');
            $table->string('public_base_url')->default('https://api.sharkexchange.in');
            $table->string('default_symbol')->default('BTCINR');
            $table->string('margin_asset')->default('INR');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shark_account_id')->nullable()->constrained('shark_accounts')->nullOnDelete();
            $table->string('status');
            $table->text('message')->nullable();
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('orders_count')->default(0);
            $table->unsignedInteger('positions_count')->default(0);
            $table->json('wallet_snapshot')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('shark_accounts');
    }

    private function addColumnIfMissing(Blueprint $table, string $name, string $type, array $options = []): void
    {
        if (Schema::hasColumn('trades', $name)) {
            return;
        }

        $column = match ($type) {
            'decimal' => $table->decimal($name, $options['total'], $options['places']),
            'unsignedTinyInteger' => $table->unsignedTinyInteger($name),
            'boolean' => $table->boolean($name),
            'json' => $table->json($name),
            'timestamp' => $table->timestamp($name),
            default => $table->{$type}($name),
        };

        if (($options['nullable'] ?? false) === true) {
            $column->nullable();
        }

        if (array_key_exists('default', $options)) {
            $column->default($options['default']);
        }
    }
};
