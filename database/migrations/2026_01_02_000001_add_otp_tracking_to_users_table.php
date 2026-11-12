
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code')->nullable();
            }

            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'otp_attempts')) {
                $table->unsignedTinyInteger('otp_attempts')->default(0);
            }

            if (!Schema::hasColumn('users', 'otp_sent_at')) {
                $table->timestamp('otp_sent_at')->nullable();
            }
        });

        // Check if duplicate ID numbers exist
        if (Schema::hasColumn('users', 'id_number')) {

            $duplicates = DB::table('users')
                ->select('id_number')
                ->whereNotNull('id_number')
                ->groupBy('id_number')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            $indexes = collect(Schema::getIndexes('users'))
                ->pluck('name');

            // Add unique index only if there are no duplicates
            if (
                !$duplicates &&
                !$indexes->contains('users_id_number_unique')
            ) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('id_number');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'otp_attempts')) {
                $table->dropColumn('otp_attempts');
            }

            if (Schema::hasColumn('users', 'otp_sent_at')) {
                $table->dropColumn('otp_sent_at');
            }
        });
    }
};
