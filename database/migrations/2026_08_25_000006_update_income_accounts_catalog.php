<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('income_accounts', function (Blueprint $table) {
            $table->string('budget_account', 150)->after('id');
            $table->string('accounting_account', 150)->after('budget_account');
            $table->string('concept', 255)->after('accounting_account');
        });

        Schema::table('income_accounts', function (Blueprint $table) {
            $table->dropUnique(['account']);
            $table->dropColumn(['account', 'description']);
        });

        Schema::table('income_accounts', function (Blueprint $table) {
            $table->unique(['budget_account', 'accounting_account', 'concept']);
        });
    }

    public function down(): void
    {
        Schema::table('income_accounts', function (Blueprint $table) {
            $table->string('account', 150)->nullable();
            $table->string('description', 255)->nullable();
        });

        Schema::table('income_accounts', function (Blueprint $table) {
            $table->dropUnique(['budget_account', 'accounting_account', 'concept']);
            $table->dropColumn(['budget_account', 'accounting_account', 'concept']);
        });
    }
};
