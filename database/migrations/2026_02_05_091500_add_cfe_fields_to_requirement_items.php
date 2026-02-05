<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('requirement_items', function (Blueprint $table) {
            $table->string('cfe_rpu')->nullable()->after('amount');
            $table->string('cfe_town')->nullable()->after('cfe_rpu');
            $table->string('cfe_address')->nullable()->after('cfe_town');
            $table->string('cfe_uuid')->nullable()->after('cfe_address');
            $table->date('cfe_period_start')->nullable()->after('cfe_uuid');
            $table->date('cfe_period_end')->nullable()->after('cfe_period_start');
            $table->decimal('cfe_subtotal', 10, 2)->nullable()->after('cfe_period_end');
            $table->decimal('cfe_iva', 10, 2)->nullable()->after('cfe_subtotal');
            $table->decimal('cfe_rounding', 10, 2)->nullable()->after('cfe_iva');
        });
    }

    public function down()
    {
        Schema::table('requirement_items', function (Blueprint $table) {
            $table->dropColumn([
                'cfe_rpu',
                'cfe_town',
                'cfe_address',
                'cfe_uuid',
                'cfe_period_start',
                'cfe_period_end',
                'cfe_subtotal',
                'cfe_iva',
                'cfe_rounding',
            ]);
        });
    }
};
