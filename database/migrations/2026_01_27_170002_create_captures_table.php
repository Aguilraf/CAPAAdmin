<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('captures', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('year'); // Added from consolidated migration
            $table->foreignId('community_id')->constrained();
            $table->foreignId('firefighter_id')->constrained();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('rounding_commission', 10, 2);
            $table->decimal('rounding_total', 10, 2);

            // Consolidated fields
            $table->string('requirement_number')->nullable();
            $table->date('assignment_date')->nullable();

            $table->timestamps();

            $table->index('requirement_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captures');
    }
};
