<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->date('operation_date');
            $table->date('application_date')->nullable();
            $table->string('movement_number')->nullable();
            $table->string('reference')->nullable();
            $table->string('transaction_type')->nullable();
            $table->text('description')->nullable();
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->nullable();
            $table->string('source_file')->nullable();
            $table->string('fingerprint', 64)->unique();
            $table->json('source_data')->nullable();
            $table->timestamps();

            $table->index(['bank_id', 'operation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_movements');
    }
};
