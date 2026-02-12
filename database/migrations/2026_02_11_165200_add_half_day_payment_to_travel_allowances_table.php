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
        Schema::table('travel_allowances', function (Blueprint $table) {
            $table->boolean('half_day_payment')->default(false)->after('days_duration')->comment('Indica si se paga medio día');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_allowances', function (Blueprint $table) {
            $table->dropColumn('half_day_payment');
        });
    }
};
