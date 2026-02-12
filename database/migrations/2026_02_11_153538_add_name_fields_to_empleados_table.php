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
            $table->string('primer_nombre')->nullable()->after('nombre')->comment('Primer nombre del empleado');
            $table->string('primer_apellido')->nullable()->after('primer_nombre')->comment('Primer apellido del empleado');
            $table->string('segundo_apellido')->nullable()->after('primer_apellido')->comment('Segundo apellido del empleado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn(['primer_nombre', 'primer_apellido', 'segundo_apellido']);
        });
    }
};
