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
            $table->string('nivel')->nullable()->after('fecha_alta');
            $table->string('nss')->nullable()->after('curp');
            $table->string('afiliacion')->nullable()->after('nss')->comment('e.g. ISSSTE, IMSS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn(['nivel', 'nss', 'afiliacion']);
        });
    }
};
