<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('daily_income_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_reconciled_without_income')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['daily_income_id']);
            $table->dropColumn(['daily_income_id', 'is_reconciled_without_income']);
        });
    }
};
