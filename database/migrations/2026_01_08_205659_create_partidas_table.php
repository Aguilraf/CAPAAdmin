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
        Schema::create('partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capitulo_id')->constrained('capitulos')->onDelete('cascade')->comment('Relación con capítulo');
            $table->string('codigo')->comment('Código de la partida (ej: 29,601)');
            $table->string('nombre')->comment('Nombre de la partida');
            $table->text('descripcion')->nullable()->comment('Descripción detallada');
            $table->boolean('activo')->default(true)->comment('Partida activa o inactiva');
            $table->timestamps();
            $table->softDeletes();

            // Índice compuesto para búsquedas rápidas
            $table->index(['capitulo_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};
