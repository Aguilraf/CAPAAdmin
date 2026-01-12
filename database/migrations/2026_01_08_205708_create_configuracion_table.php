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
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_empresa')->default('COMISION DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO')->comment('Nombre de la empresa');
            $table->string('nombre_organismo')->default('ORGANISMO OPERADOR JOSE MARIA MORELOS')->comment('Nombre del organismo operador');
            $table->text('logo')->nullable()->comment('Ruta del logo');
            $table->decimal('iva', 5, 2)->default(16.00)->comment('Porcentaje de IVA');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
