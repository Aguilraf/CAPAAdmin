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
        Schema::create('capitulos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código del capítulo (ej: 2000)');
            $table->string('nombre')->comment('Nombre del capítulo');
            $table->text('descripcion')->nullable()->comment('Descripción detallada');
            $table->boolean('activo')->default(true)->comment('Capítulo activo o inactivo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capitulos');
    }
};
