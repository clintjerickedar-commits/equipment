<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('borrow_date');
            $table->date('expected_return_date')->index();
            $table->text('purpose');
            // pending | approved | rejected | returned | overdue | cancelled
            $table->string('status', 20)->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('borrowing_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_request_id')->constrained('borrowing_requests')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['borrowing_request_id', 'equipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowing_request_items');
        Schema::dropIfExists('borrowing_requests');
    }
};
