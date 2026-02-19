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
        Schema::table('travel_allowance_commissioners', function (Blueprint $table) {
            $table->date('report_date')->after('employee_id')->nullable();
            $table->string('report_link')->after('report_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_allowance_commissioners', function (Blueprint $table) {
            $table->dropColumn(['report_date', 'report_link']);
        });
    }
};
