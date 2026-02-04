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
        // 1. Periodos Vacacionales (2 per year)
        Schema::create('periodos_vacacionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->integer('anio'); // e.g., 2026
            $table->integer('numero_periodo'); // 1 or 2
            $table->date('fecha_inicio')->nullable(); // Start of period validity
            $table->date('fecha_fin')->nullable(); // End of period validity
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empleado_id', 'anio', 'numero_periodo']);
        });

        // 2. Saldos de Vacaciones (The "Buckets": Ordinario, Antiguedad, SUTECAPA)
        Schema::create('saldos_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_vacacional_id')->constrained('periodos_vacacionales')->onDelete('cascade');
            $table->string('tipo'); // ORDINARIO, ANTIGUEDAD, SUTECAPA
            $table->integer('total_dias');
            $table->integer('dias_usados')->default(0);
            $table->integer('dias_pendientes')->default(0); // Reserved in pending requests
            $table->timestamps();
        });

        // 3. Bonos por Evaluación (Quarterly)
        Schema::create('bonos_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->integer('anio');
            $table->integer('cuatrimestre'); // 1, 2, 3
            $table->decimal('calificacion', 5, 2);
            $table->integer('dias_otorgados'); // 1, 2, 3
            $table->integer('dias_usados')->default(0);
            $table->date('fecha_expiracion')->nullable(); // Valid for next 4 months
            $table->timestamps();
        });

        // 4. Solicitudes de Vacaciones (The main request)
        Schema::create('solicitudes_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->string('tipo_solicitud')->default('VACACION'); // VACACION, ONOMASTICO, DEFUNCION, NACIMIENTO
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('dias_solicitados');
            $table->text('motivo')->nullable();
            $table->enum('estado', ['PENDIENTE', 'APROBADA', 'RECHAZADA', 'CANCELADA'])->default('PENDIENTE');
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->datetime('fecha_aprobacion')->nullable();
            $table->text('comentarios_rechazo')->nullable();
            $table->timestamps();
        });

        // 5. Detalle de Solicitud (Link logic to balances for "Oficios")
        Schema::create('detalles_solicitud', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_vacaciones')->onDelete('cascade');

            // Polymorphic relation to either SaldoVacacion or BonoEvaluacion
            // We can simplify by having nullable FKs or a type string + ID
            $table->string('origen_tipo'); // 'App\Models\SaldoVacacion' or 'App\Models\BonoEvaluacion' or 'SPECIAL'
            $table->unsignedBigInteger('origen_id')->nullable();

            $table->integer('dias_tomados');
            $table->string('numero_oficio')->nullable(); // The generated document number
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_solicitud');
        Schema::dropIfExists('solicitudes_vacaciones');
        Schema::dropIfExists('bonos_evaluacion');
        Schema::dropIfExists('saldos_vacaciones');
        Schema::dropIfExists('periodos_vacacionales');
    }
};
