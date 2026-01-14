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
        Schema::create('reporte_bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->onDelete('set null');

            // Report details
            $table->date('fecha_reporte');
            $table->string('destinatario_nombre');
            $table->string('destinatario_cargo');
            $table->string('solicitante_nombre');
            $table->string('solicitante_cargo');
            $table->string('solicitante_departamento')->nullable();

            // Materials data stored as JSON
            $table->json('materiales');

            // Full report data for reference
            $table->json('datos_completos');

            $table->timestamps();

            // Indexes for searching
            $table->index('fecha_reporte');
            $table->index('user_id');
            $table->index('empleado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte_bitacoras');
    }
};
