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
        if (!Schema::hasTable('travel_allowances')) {
            Schema::create('travel_allowances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requirement_id')->constrained()->onDelete('cascade');

                // Commission Details
                $table->string('oficio_number')->nullable();
                $table->text('commission_summary_legend')->nullable();
                $table->year('exercise_year')->nullable();
                $table->enum('quarter', ['I', 'II', 'III', 'IV'])->nullable();

                // Employee / Commissioner
                $table->foreignId('commissioner_id')->constrained('empleados');

                // Origin & Destination
                $table->string('origin_country')->default('México');
                $table->string('origin_state')->default('Quintana Roo');
                $table->string('origin_city')->default('José María Morelos');

                $table->string('destination_country')->default('México');
                $table->string('destination_state');
                $table->string('destination_city');

                // Dates & Duration
                $table->dateTime('departure_date');
                $table->dateTime('return_date');
                $table->integer('days_duration')->nullable();

                $table->text('justification')->nullable();

                // Expense Types & Budget Items (Partidas)
                $table->boolean('has_viaticos')->default(false);
                $table->foreignId('viaticos_partida_id')->nullable()->constrained('partidas');

                $table->boolean('has_pasaje')->default(false);
                $table->foreignId('pasaje_partida_id')->nullable()->constrained('partidas');

                $table->boolean('has_hospedaje')->default(false);
                $table->foreignId('hospedaje_partida_id')->nullable()->constrained('partidas');

                // Transport
                $table->enum('transport_type', ['Oficial', 'Particular', 'Publico'])->default('Oficial');
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');

                // Invoice / Fiscal Data
                $table->string('invoice_folio')->nullable();
                $table->date('invoice_date')->nullable();
                $table->string('provider_rfc')->nullable();

                // Amounts
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('iva', 10, 2)->default(0);
                $table->decimal('isr', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_allowances');
    }
};
