<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_income_details', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_income_details', 'daily_income_id')) {
                $table->foreignId('daily_income_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (!Schema::hasColumn('daily_income_details', 'bank_movement_id')) {
                $table->foreignId('bank_movement_id')->nullable()->after('daily_income_id')->constrained()->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_income_details', function (Blueprint $table) {
            if (Schema::hasColumn('daily_income_details', 'bank_movement_id')) {
                $table->dropConstrainedForeignId('bank_movement_id');
            }

            if (Schema::hasColumn('daily_income_details', 'daily_income_id')) {
                $table->dropConstrainedForeignId('daily_income_id');
            }
        });
    }
};
