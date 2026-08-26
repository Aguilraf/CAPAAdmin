<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_incomes', function (Blueprint $table) {
            $table->id();
            $table->date('income_date');
            $table->decimal('total_amount', 15, 2);
            $table->integer('total_movements');
            $table->timestamps();
        });

        Schema::create('daily_income_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_income_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_movement_id')->constrained();
            $table->timestamps();
        });

        Schema::table('bank_movements', function (Blueprint $table) {
            $table->boolean('is_used')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('bank_movements', function (Blueprint $table) {
            $table->dropColumn('is_used');
        });
        Schema::dropIfExists('daily_income_details');
        Schema::dropIfExists('daily_incomes');
    }
};
