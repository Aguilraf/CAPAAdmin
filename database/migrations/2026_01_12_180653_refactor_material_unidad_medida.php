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
        // 1. Add null FK column
        Schema::table('materials', function (Blueprint $table) {
            $table->foreignId('unidad_medida_id')->nullable()->after('cantidad')->constrained('unidad_medidas')->onDelete('set null');
        });

        // 2. Migrate existing data
        // Get all materials
        $materials = \Illuminate\Support\Facades\DB::table('materials')->get();

        foreach ($materials as $material) {
            if ($material->unidad_medida) {
                // Find or create the unit
                $unit = \Illuminate\Support\Facades\DB::table('unidad_medidas')
                    ->where('nombre', $material->unidad_medida)
                    ->first();

                if (!$unit) {
                    $id = \Illuminate\Support\Facades\DB::table('unidad_medidas')->insertGetId([
                        'nombre' => $material->unidad_medida,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $unitId = $id;
                } else {
                    $unitId = $unit->id;
                }

                // Update material
                \Illuminate\Support\Facades\DB::table('materials')
                    ->where('id', $material->id)
                    ->update(['unidad_medida_id' => $unitId]);
            }
        }

        // 3. Drop old column
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('unidad_medida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('unidad_medida')->default('unidad')->after('cantidad');
        });

        // Restore data (simplified, just format strings)
        $materials = \Illuminate\Support\Facades\DB::table('materials')->get();
        foreach ($materials as $material) {
            if ($material->unidad_medida_id) {
                $unit = \Illuminate\Support\Facades\DB::table('unidad_medidas')->where('id', $material->unidad_medida_id)->first();
                if ($unit) {
                    \Illuminate\Support\Facades\DB::table('materials')
                        ->where('id', $material->id)
                        ->update(['unidad_medida' => $unit->nombre]);
                }
            }
        }

        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['unidad_medida_id']);
            $table->dropColumn('unidad_medida_id');
        });
    }
};
