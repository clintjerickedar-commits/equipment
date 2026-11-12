<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_code')->unique();               // e.g. EQ-0001
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity');                      // total units owned
            $table->unsignedInteger('available_quantity');            // units on the shelf
            $table->string('image_path')->nullable();
            $table->string('condition', 20)->default('good');         // good | fair | needs_repair
            $table->string('status', 20)->default('active')->index(); // active | maintenance | retired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
