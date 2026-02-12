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
            $table->string('clabe')->nullable()->after('rfc');
            $table->string('tipo_plaza')->nullable()->after('puesto');
            $table->string('area_adscripcion')->nullable()->after('departamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn(['clabe', 'tipo_plaza', 'area_adscripcion']);
        });
    }
};
