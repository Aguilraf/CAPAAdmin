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
        Schema::table('requirements', function (Blueprint $table) {
            $table->string('revolving_fund_type')->nullable()->after('type'); // Reposición o Cancelación
            $table->string('revolving_fund_number')->nullable()->after('requirement_number');
        });

        Schema::table('requirement_items', function (Blueprint $table) {
            $table->decimal('invoice_discount', 10, 2)->nullable()->after('invoice_iva');
            $table->decimal('invoice_ieps', 10, 2)->nullable()->after('invoice_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropColumn(['revolving_fund_type', 'revolving_fund_number']);
        });

        Schema::table('requirement_items', function (Blueprint $table) {
            $table->dropColumn(['invoice_discount', 'invoice_ieps']);
        });
    }
};
