<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Drop old tables (Order matters due to potential constraints, though mostly polymorphic)
        Schema::dropIfExists('detalles_solicitud');
        Schema::dropIfExists('saldos_vacaciones');
        Schema::dropIfExists('periodos_vacacionales');
        Schema::dropIfExists('bonos_evaluacion');

        // 2. Create Unified 'Entitlements' Table
        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');

            $table->integer('year'); // Año del derecho
            $table->string('type'); // ORDINARIO, ANTIGUEDAD, SUTECAPA, BONO_CUATRIMESTRAL
            $table->string('description')->nullable(); // Metadata: 'Periodo 1', 'Bono 2do C.'

            $table->integer('total_days');
            $table->integer('used_days')->default(0);
            $table->integer('pending_days')->default(0); // Reserved

            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->string('status')->default('ACTIVE'); // ACTIVE, EXPIRED, EXHAUSTED
            $table->json('meta')->nullable(); // Store specific metadata like 'cuatrimestre', 'calificacion'

            $table->timestamps();
        });

        // 3. Create Pivot Table for Usage (Replacing DetallesSolicitud)
        Schema::create('request_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_vacaciones')->onDelete('cascade');
            $table->foreignId('entitlement_id')->constrained('entitlements')->onDelete('cascade');

            $table->integer('days_taken');
            $table->string('numero_oficio')->nullable(); // Optional: if we want to track breakdown

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_entitlements');
        Schema::dropIfExists('entitlements');

        // Note: We cannot easily recreate the data structure of the old tables without strict schema definitions here.
        // For development iteration, ensuring we can just roll back to 'clean state' is enough, or we'd duplicate the create logic from the previous migration.
        // Re-creating empty tables to satisfy 'rollback':

        // (Simplified re-creation for rollback safety)
        Schema::create('periodos_vacacionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id');
            $table->timestamps();
        });
        Schema::create('saldos_vacaciones', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
        Schema::create('bonos_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
        Schema::create('detalles_solicitud', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
