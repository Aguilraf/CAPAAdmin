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
        Schema::table('partidas', function (Blueprint $table) {
            $table->text('nombre')->change();
        });

        Schema::table('capitulos', function (Blueprint $table) {
            $table->text('nombre')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            $table->string('nombre')->change();
        });

        Schema::table('capitulos', function (Blueprint $table) {
            $table->string('nombre')->change();
        });
    }
};
