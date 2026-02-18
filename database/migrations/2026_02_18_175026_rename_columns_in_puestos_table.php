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
        Schema::table('puestos', function (Blueprint $table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('level', 'nivel');
            $table->renameColumn('description', 'descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('nivel', 'level');
            $table->renameColumn('descripcion', 'description');
        });
    }
};
