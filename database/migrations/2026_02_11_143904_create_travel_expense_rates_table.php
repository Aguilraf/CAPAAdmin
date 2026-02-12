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
        Schema::create('travel_expense_rates', function (Blueprint $table) {
            $table->id();
            $table->string('role_level'); // Nivel del puesto
            $table->string('concept'); // Viaticos, Hospedaje, Transporte
            $table->decimal('zone_i_limit', 10, 2)->default(0);
            $table->decimal('zone_ii_limit', 10, 2)->default(0);
            $table->date('effective_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_expense_rates');
    }
};
