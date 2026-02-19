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
            $table->unsignedBigInteger('employee_id')->nullable()->after('partida_id');
            $table->string('uuid')->nullable()->after('amount');
            $table->string('invoice_folio')->nullable()->after('uuid');
            $table->date('invoice_date')->nullable()->after('invoice_folio');
            $table->string('provider_rfc')->nullable()->after('invoice_date');
            $table->string('provider_name')->nullable()->after('provider_rfc');

            $table->foreign('employee_id')->references('id')->on('empleados')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirement_items', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'uuid', 'invoice_folio', 'invoice_date', 'provider_rfc', 'provider_name']);
        });
    }
};
