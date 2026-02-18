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
        Schema::table('organismos', function (Blueprint $table) {
            $table->text('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('foto')->nullable();
            $table->string('ubicacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organismos', function (Blueprint $table) {
            $table->dropColumn(['direccion', 'telefono', 'correo', 'foto', 'ubicacion']);
        });
    }
};
