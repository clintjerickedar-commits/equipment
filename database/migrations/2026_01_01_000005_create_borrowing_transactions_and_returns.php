<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Created only when a request is approved
        Schema::create('borrowing_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_request_id')->unique()->constrained('borrowing_requests')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at');
            $table->date('due_date')->index();
            $table->string('status', 20)->default('active')->index(); // active | overdue | returned
            $table->timestamps();
        });

        // UNIQUE(borrowing_transaction_id) => a transaction can be returned only once
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_transaction_id')->unique()->constrained('borrowing_transactions')->cascadeOnDelete();
            $table->date('actual_return_date');
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->unsignedInteger('quantity_borrowed');
            $table->unsignedInteger('quantity_returned_good')->default(0);
            $table->unsignedInteger('quantity_damaged')->default(0);
            $table->unsignedInteger('quantity_missing')->default(0);
            $table->string('condition_on_return', 20)->default('good'); // good | fair | damaged
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('borrowing_transactions');
    }
};
