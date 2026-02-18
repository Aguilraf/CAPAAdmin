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
            $table->string('provider_name')->nullable()->after('provider_rfc');
            $table->string('uuid')->nullable()->after('invoice_folio');
            $table->decimal('retention_iva', 10, 2)->default(0)->after('isr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_allowances', function (Blueprint $table) {
            $table->dropColumn(['provider_name', 'uuid', 'retention_iva']);
        });
    }
};
