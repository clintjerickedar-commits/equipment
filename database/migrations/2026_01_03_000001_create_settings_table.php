<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'key')) {
                    $table->string('key')->nullable();
                }

                if (!Schema::hasColumn('settings', 'value')) {
                    $table->text('value')->nullable();
                }

                if (!Schema::hasColumn('settings', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (!Schema::hasColumn('settings', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        $now = now();

        foreach ([
            'max_borrow_days' => '14',
            'max_quantity_per_item' => '0',
        ] as $key => $value) {
            if (Schema::hasColumn('settings', 'key')
                && Schema::hasColumn('settings', 'value')
                && !DB::table('settings')->where('key', $key)->exists()) {
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
        // Preserve the existing settings table and its data.
    }
};