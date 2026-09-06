<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->decimal('dni_amount', 15, 2)->default(0)->after('draef_iva');
        });
    }

    public function down(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->dropColumn('dni_amount');
        });
    }
};
