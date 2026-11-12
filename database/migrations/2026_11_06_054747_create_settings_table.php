<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        if (!Schema::hasColumn('settings', 'key') ||
            !Schema::hasColumn('settings', 'value')) {
            return;
        }

        $now = now();

        foreach ([
            'max_borrow_days' => '14',
            'max_quantity_per_item' => '0',
        ] as $key => $value) {
            if (!DB::table('settings')->where('key', $key)->exists()) {
                DB::table('settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Preserve existing settings data.
    }
};