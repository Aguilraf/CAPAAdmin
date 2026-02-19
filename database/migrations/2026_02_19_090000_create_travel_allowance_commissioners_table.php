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
        Schema::create('travel_allowance_commissioners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_allowance_id')->constrained('travel_allowances')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('empleados')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_allowance_commissioners');
    }
};
