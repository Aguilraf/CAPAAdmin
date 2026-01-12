<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique()->comment('Clave única del empleado');
            $table->string('nombre')->comment('Nombre completo del empleado');
            $table->string('puesto')->comment('Puesto del empleado');
            $table->string('departamento')->comment('Departamento al que pertenece');
            $table->string('rfc', 13)->nullable()->comment('RFC del empleado');
            $table->string('categoria')->nullable()->comment('Categoría del empleado');
            $table->date('fecha_alta')->nullable()->comment('Fecha de alta');
            $table->decimal('salario_diario', 10, 2)->nullable()->comment('Salario diario');
            $table->decimal('salario_mensual', 10, 2)->nullable()->comment('Salario mensual');
            $table->string('curp', 18)->nullable()->comment('CURP del empleado');
            $table->string('email')->nullable()->comment('Correo electrónico');
            $table->string('telefono', 20)->nullable()->comment('Teléfono de contacto');
            $table->string('numero_empleado')->nullable()->comment('Número de empleado');
            $table->text('fotografia')->nullable()->comment('Ruta de la fotografía');
            $table->date('fecha_baja')->nullable()->comment('Fecha de baja (si aplica)');
            $table->boolean('activo')->default(true)->comment('Empleado activo o inactivo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
