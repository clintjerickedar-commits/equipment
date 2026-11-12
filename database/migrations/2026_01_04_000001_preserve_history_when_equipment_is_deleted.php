<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * WHY deleting equipment was blocked:
 *   borrowing_request_items.equipment_id and return_items.equipment_id were NOT NULL foreign keys with
 *   ON DELETE RESTRICT, so MySQL refuses to delete any equipment row that history still points at.
 *
 * THE FIX (nothing is deleted or rewritten except the two column definitions):
 *   1. equipment_name + equipment_code snapshot columns, filled for every existing row from the live equipment;
 *   2. equipment_id becomes nullable and the foreign key becomes ON DELETE SET NULL.
 * Deleting equipment now removes only the equipment row. Requests, transactions and returns stay, and still
 * show the equipment name and code from the snapshot.
 *
 * Safe to run on your current database with `php artisan migrate`. It can also be re-run after a failed attempt.
 */
return new class extends Migration
{
    private array $tables = ['borrowing_request_items', 'return_items'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            // 1) snapshot columns
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'equipment_name')) {
                    $t->string('equipment_name')->nullable()->after('equipment_id');
                }
                if (!Schema::hasColumn($table, 'equipment_code')) {
                    $t->string('equipment_code', 50)->nullable()->after('equipment_name');
                }
            });

            // 2) nullable column + ON DELETE SET NULL (skipped if a previous run already did it)
            $nullable = (bool) (collect(Schema::getColumns($table))->firstWhere('name', 'equipment_id')['nullable'] ?? false);
            $hasFk    = $this->hasEquipmentForeignKey($table);

            if ($nullable && $hasFk) {
                continue;
            }

            if ($hasFk) {
                Schema::table($table, fn (Blueprint $t) => $t->dropForeign(['equipment_id']));
            }
            Schema::table($table, fn (Blueprint $t) => $t->unsignedBigInteger('equipment_id')->nullable()->change());
            Schema::table($table, fn (Blueprint $t) => $t->foreign('equipment_id')->references('id')->on('equipment')->nullOnDelete());
        }

        // 3) fill the snapshot of every existing history row from the equipment it points at
        foreach (DB::table('equipment')->get(['id', 'name', 'equipment_code']) as $equipment) {
            foreach ($this->tables as $table) {
                DB::table($table)->where('equipment_id', $equipment->id)->whereNull('equipment_name')
                    ->update(['equipment_name' => $equipment->name, 'equipment_code' => $equipment->equipment_code]);
            }
        }
    }

    public function down(): void
    {
        // The nullable foreign key is kept on purpose (restoring NOT NULL would fail once equipment was deleted).
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                foreach (['equipment_name', 'equipment_code'] as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $t->dropColumn($column);
                    }
                }
            });
        }
    }

    private function hasEquipmentForeignKey(string $table): bool
    {
        return collect(Schema::getForeignKeys($table))->contains(fn ($fk) => in_array('equipment_id', $fk['columns'], true));
    }
};
