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
        Schema::table('requirement_items', function (Blueprint $table) {
            $table->decimal('invoice_subtotal', 10, 2)->nullable()->after('provider_name');
            $table->decimal('invoice_iva', 10, 2)->nullable()->after('invoice_subtotal');
            $table->decimal('invoice_retention_isr', 10, 2)->nullable()->after('invoice_iva');
            $table->decimal('invoice_retention_iva', 10, 2)->nullable()->after('invoice_retention_isr');
            $table->decimal('invoice_total', 10, 2)->nullable()->after('invoice_retention_iva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirement_items', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_subtotal',
                'invoice_iva',
                'invoice_retention_isr',
                'invoice_retention_iva',
                'invoice_total'
            ]);
        });
    }
};
