<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->decimal('draef_subtotal', 15, 2)->default(0)->after('draef_amount');
            $table->decimal('draef_iva', 15, 2)->default(0)->after('draef_subtotal');
        });

        // Los registros existentes solo tenían el total capturado, se conserva como facturación sin IVA.
        DB::table('daily_incomes')->where('draef_amount', '>', 0)->update([
            'draef_subtotal' => DB::raw('draef_amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('daily_incomes', function (Blueprint $table) {
            $table->dropColumn(['draef_subtotal', 'draef_iva']);
        });
    }
};
