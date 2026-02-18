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
            $table->foreignId('puesto_id')->nullable()->after('puesto')->constrained('puestos')->nullOnDelete();
        });

        // Migrate existing Puestos
        $empleados = \DB::table('empleados')->whereNotNull('puesto')->where('puesto', '!=', '')->get();
        foreach ($empleados as $empleado) {
            $puestoName = trim($empleado->puesto);
            if (!$puestoName)
                continue;

            $puesto = \DB::table('puestos')->where('name', $puestoName)->first();
            if (!$puesto) {
                $id = \DB::table('puestos')->insertGetId([
                    'name' => $puestoName,
                    'level' => $empleado->nivel,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $puestoId = $id;
            } else {
                $puestoId = $puesto->id;
            }

            \DB::table('empleados')->where('id', $empleado->id)->update(['puesto_id' => $puestoId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign(['puesto_id']);
            $table->dropColumn('puesto_id');
        });
    }
};
