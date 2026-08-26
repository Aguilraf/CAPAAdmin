<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_number');
            $table->string('account_name')->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->string('import_template')->default('custom');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'account_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
