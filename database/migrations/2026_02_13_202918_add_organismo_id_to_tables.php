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
        $tables = ['users', 'empleados', 'communities', 'firefighters'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('organismo_id')->nullable()->after('id');
                    $table->foreign('organismo_id')->references('id')->on('organismos');
                });

                // Update existing records to belong to default Organismo (ID: 1)
                DB::table($tableName)->update(['organismo_id' => 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'empleados', 'communities', 'firefighters'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['organismo_id']);
                    $table->dropColumn('organismo_id');
                });
            }
        }
    }
};
