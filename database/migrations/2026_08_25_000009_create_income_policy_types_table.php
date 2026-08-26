<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_policy_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('income_policy_types')->insert([
            ['name' => 'Ingreso', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ingreso extraordinario', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Traspaso', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Otro', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('income_policy_types');
    }
};
