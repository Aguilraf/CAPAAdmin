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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_number')->nullable();
            $table->string('unit_number')->nullable();
            $table->string('brand')->nullable(); // MARCA
            $table->string('type')->nullable(); // TIPO
            $table->string('color')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('motor_number')->nullable();
            $table->string('assignee_area')->nullable(); // AREA
            $table->string('plate')->nullable(); // PLACA ACTUAL
            $table->string('resguardante')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
