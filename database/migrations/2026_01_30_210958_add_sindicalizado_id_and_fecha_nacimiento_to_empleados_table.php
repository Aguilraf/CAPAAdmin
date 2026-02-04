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
        Schema::table('empleados', function (Blueprint $table) {
            $table->boolean('es_sindicalizado')->default(false)->after('activo')->comment('Indica si el empleado pertenece al sindicato');
            $table->date('fecha_nacimiento')->nullable()->after('fecha_alta')->comment('Fecha de nacimiento para cálculo de onomástico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn(['es_sindicalizado', 'fecha_nacimiento']);
        });
    }
};
