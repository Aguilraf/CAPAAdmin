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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('beneficiary_type')->nullable()->after('payment_date'); // 'employee', 'provider'
            $table->unsignedBigInteger('beneficiary_id')->nullable()->after('beneficiary_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['beneficiary_type', 'beneficiary_id']);
        });
    }
};
