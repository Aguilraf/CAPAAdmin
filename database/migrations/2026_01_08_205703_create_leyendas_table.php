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
        Schema::create('leyendas', function (Blueprint $table) {
            $table->id();
            $table->year('anio')->unique()->comment('Año de la leyenda');
            $table->text('texto')->comment('Texto de la leyenda');
            $table->boolean('activa')->default(false)->comment('Leyenda activa para usar por defecto');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leyendas');
    }
};
