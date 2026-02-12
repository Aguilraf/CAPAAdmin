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
        Schema::create('travel_allowance_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('partidas')->onDelete('cascade');
            $table->string('cargo')->comment('Employee position/cargo');
            $table->string('nivel')->comment('Employee level');
            $table->decimal('zona_1_amount', 10, 2)->comment('Amount for Zone I');
            $table->decimal('zona_2_amount', 10, 2)->comment('Amount for Zone II');
            $table->enum('rate_type', ['viaticos', 'pasajes', 'hospedaje'])->comment('Type of allowance');
            $table->integer('year')->comment('Fiscal year for this rate');
            $table->boolean('active')->default(true)->comment('Whether this rate is currently active');
            $table->timestamps();
            $table->softDeletes();

            // Composite index for fast lookups
            $table->index(['partida_id', 'cargo', 'nivel', 'rate_type', 'year', 'active'], 'tar_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_allowance_rates');
    }
};
