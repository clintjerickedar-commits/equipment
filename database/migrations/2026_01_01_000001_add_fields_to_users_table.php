<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('borrower')->after('password')->index();
            $table->string('phone', 30)->nullable()->after('role');
            $table->string('id_number', 50)->nullable()->after('phone'); // student / employee ID
            $table->boolean('is_active')->default(true)->after('id_number');
            $table->string('otp_code')->nullable()->after('is_active');      // stored hashed
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'id_number', 'is_active', 'otp_code', 'otp_expires_at']);
        });
    }
};
