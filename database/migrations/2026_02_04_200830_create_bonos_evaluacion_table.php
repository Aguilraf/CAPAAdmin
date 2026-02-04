<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bonos_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->integer('anio');
            $table->integer('cuatrimestre'); // 1, 2, 3
            $table->decimal('calificacion', 5, 2)->nullable(); // 0-100 or specific score
            $table->integer('dias_otorgados')->default(0);
            $table->integer('dias_usados')->default(0);
            $table->date('fecha_expiracion');
            $table->timestamps();

            // Constraint: One evaluation per period per employee
            $table->unique(['empleado_id', 'anio', 'cuatrimestre']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonos_evaluacion');
    }
};
