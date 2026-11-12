<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'otp_attempts')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('otp_attempts')->default(0);
            });
        }
    }

    public function down(): void
    {
        // Huwag tanggalin ang column dahil ginagamit din ito
        // ng naunang migration.
    }
};