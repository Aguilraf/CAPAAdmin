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
        Schema::table('travel_allowance_rates', function (Blueprint $table) {
            $table->string('budget_code')->nullable()->after('rate_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_allowance_rates', function (Blueprint $table) {
            $table->dropColumn('budget_code');
        });
    }
};
