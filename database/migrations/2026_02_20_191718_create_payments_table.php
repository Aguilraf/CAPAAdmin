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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organismo_id')->nullable()->constrained('organismos');
            $table->date('payment_date');
            $table->string('beneficiary');
            $table->decimal('amount', 12, 2);
            $table->string('amount_letters');
            $table->foreignId('requirement_id')->nullable()->constrained('requirements');
            $table->text('concept');
            $table->enum('payment_type', ['transferencia', 'cheque']);
            $table->string('reference'); // Número de cheque o referencia de transferencia

            // Firmas
            $table->foreignId('elaborated_by_id')->nullable()->constrained('empleados');
            $table->foreignId('formulated_by_id')->nullable()->constrained('empleados');
            $table->foreignId('authorized_by_id')->nullable()->constrained('empleados');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
