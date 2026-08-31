<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('invoices');

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // Folio Fiscal
            $table->string('rfc_emisor');
            $table->string('nombre_emisor')->nullable();
            $table->string('reg_emis')->nullable(); // Regimen Emisor
            $table->string('rfc_receptor');
            $table->string('nombre_receptor')->nullable();
            $table->string('reg_recep')->nullable(); // Regimen Receptor
            $table->string('tipo'); // I, E, P, etc. (o Ingreso/Complemento)
            $table->string('numero_factura')->nullable(); // Fact / Factura
            $table->date('fecha'); // Fecha Emisión
            
            // Importes de Factura Normal
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('ieps', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('base_16', 15, 2)->default(0);
            $table->decimal('base_8', 15, 2)->default(0);
            $table->decimal('base_0', 15, 2)->default(0);
            $table->decimal('iva_16', 15, 2)->default(0);
            $table->decimal('iva_8', 15, 2)->default(0);
            $table->decimal('isr_ret', 15, 2)->default(0);
            $table->decimal('iva_ret', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // Datos Operativos / Catálogos
            $table->string('uso')->nullable(); // G03, P01, etc.
            $table->string('forma_pago')->nullable(); // FP
            $table->string('metodo_pago')->nullable(); // MP
            $table->string('oi')->nullable(); // Objeto Impuesto
            $table->text('concepto')->nullable();
            $table->string('uuid_relacionado')->nullable(); // DOC RELACIONADO / Docto Relac
            $table->string('tiporel')->nullable(); // Tipo Relación
            $table->string('url')->nullable();

            // Campos específicos de Complemento de Pago
            $table->date('f_pago')->nullable(); // Fecha de Pago
            $table->string('num_op')->nullable(); // Número de Operación
            $table->string('cta_ordenante')->nullable();
            $table->string('cta_beneficiario')->nullable(); // Cta Benefic
            $table->integer('parc')->nullable(); // Parcialidad
            $table->decimal('s_anterior', 15, 2)->default(0); // Saldo Anterior
            $table->decimal('imp_pagado', 15, 2)->default(0); // Importe Pagado
            $table->decimal('saldo_insoluto', 15, 2)->default(0); // Saldo Insoluto

            $table->string('status')->default('PPD'); // PPD, PUE, Pagado
            $table->boolean('is_used')->default(false); // Para controlar el uso en cobranzas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
