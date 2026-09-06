<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->decimal('total_general', 15, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->dropColumn('total_general');
        });
    }
};
