<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_movements', function (Blueprint $table) {
            $table->dropUnique('bank_movements_bank_movement_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bank_movements', function (Blueprint $table) {
            $table->unique(['bank_id', 'movement_number'], 'bank_movements_bank_movement_unique');
        });
    }
};
