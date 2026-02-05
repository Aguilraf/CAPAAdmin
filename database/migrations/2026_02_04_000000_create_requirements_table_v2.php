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
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('requirement_number');
            $table->string('type'); // 'bomberos', 'revolvente', 'cfe', 'estandard'
            $table->date('assignment_date')->nullable();

            // Relaciones con empleados para firmas
            $table->foreignId('coordinator_id')->nullable()->constrained('empleados');
            $table->foreignId('director_id')->nullable()->constrained('empleados');
            $table->foreignId('manager_id')->nullable()->constrained('empleados');
            $table->foreignId('elaborator_id')->nullable()->constrained('empleados');

            // Campos condicionales según tipo
            $table->string('month_charged')->nullable(); // Bomberos
            $table->string('month_billed')->nullable(); // Bomberos

            $table->date('start_date')->nullable(); // CFE
            $table->date('end_date')->nullable(); // CFE
            $table->date('due_date')->nullable(); // CFE

            $table->text('description')->nullable(); // Concepto general

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('isr', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();

            // Unique index for year+number+deleted_at logic handled by app usually, but we can add basic index
            $table->index(['year', 'requirement_number']);
        });

        Schema::create('requirement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->foreignId('partida_id')->constrained('partidas');
            $table->text('description')->nullable(); // Descripcion personalizada opcional
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirement_items');
        Schema::dropIfExists('requirements');
    }
};
