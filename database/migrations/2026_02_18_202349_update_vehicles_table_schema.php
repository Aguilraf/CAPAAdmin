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
        Schema::table('vehicles', function (Blueprint $table) {
            // Rename columns conditionally to avoid errors if already renamed
            if (Schema::hasColumn('vehicles', 'unit_number') && !Schema::hasColumn('vehicles', 'unit_type')) {
                $table->renameColumn('unit_number', 'unit_type');
            }
            if (Schema::hasColumn('vehicles', 'type') && !Schema::hasColumn('vehicles', 'vehicle_type')) {
                $table->renameColumn('type', 'vehicle_type');
            }
            if (Schema::hasColumn('vehicles', 'model') && !Schema::hasColumn('vehicles', 'model_year')) {
                $table->renameColumn('model', 'model_year');
            }
            if (Schema::hasColumn('vehicles', 'motor_number') && !Schema::hasColumn('vehicles', 'engine_number')) {
                $table->renameColumn('motor_number', 'engine_number');
            }
            if (Schema::hasColumn('vehicles', 'assignee_area') && !Schema::hasColumn('vehicles', 'area')) {
                $table->renameColumn('assignee_area', 'area');
            }
            if (Schema::hasColumn('vehicles', 'plate') && !Schema::hasColumn('vehicles', 'plate_number')) {
                $table->renameColumn('plate', 'plate_number');
            }
            if (Schema::hasColumn('vehicles', 'resguardante') && !Schema::hasColumn('vehicles', 'custodian')) {
                $table->renameColumn('resguardante', 'custodian');
            }

            // Add new columns
            if (!Schema::hasColumn('vehicles', 'organismo_id')) {
                // Determine nullable for existing records
                $table->foreignId('organismo_id')->nullable()->after('id')->constrained('organismos')->onDelete('cascade');
            }
            if (!Schema::hasColumn('vehicles', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('engine_number');
            }
            if (!Schema::hasColumn('vehicles', 'supplier')) {
                $table->string('supplier')->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('vehicles', 'policy_number')) {
                $table->string('policy_number')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('vehicles', 'location')) {
                $table->string('location')->nullable()->after('area');
            }
            if (!Schema::hasColumn('vehicles', 'sub_location')) {
                $table->string('sub_location')->nullable()->after('location');
            }
            if (!Schema::hasColumn('vehicles', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('plate_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            //
        });
    }
};
