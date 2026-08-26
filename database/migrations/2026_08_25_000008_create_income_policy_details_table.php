<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('income_policies', function (Blueprint $table) {
            $table->string('account', 150)->nullable()->change();
            $table->text('concept')->nullable()->change();
            $table->decimal('amount', 15, 2)->default(0)->change();
        });

        Schema::create('income_policy_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('income_policy_id')->constrained('income_policies')->cascadeOnDelete();
            $table->foreignId('income_account_id')->constrained('income_accounts');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
            $table->unique(['income_policy_id', 'income_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_policy_details');
    }
};
