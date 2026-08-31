<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\InvoiceImportService;
use App\Models\Invoice;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_import_service_with_fake_csv(): void
    {
        // Simulamos las cabeceras exactas de tu Excel de doble sección
        $headers = [
            'FOLIO FISCAL', 'RFC EMISOR', 'RFC RECEPTOR', 'TIPO', 'FACTURA', 'Fecha', 'SUBTOTAL', 'I.V.A 16', 'I.V.A 8', 'TOTAL', 'FP', 'CONCEPTO', 'UUID', 'URL', 'METO', // Sección blanca (0 a 14)
            'DOC RELAC', 'RFC EMISOR REL', 'RFC RECEPTOR REL', 'TIPO COMPLEMENTO', 'FACTURA COMPLEMENTO', 'FECHA COMPLEMENTO', 'SUBTOTAL COMP', 'I.V.A COMP', 'BLANK', 'TOTAL COMPLEMENTO' // Sección durazno (15 a 24)
        ];

        // Fila 1: Factura normal PUE (Total 61,683.41, marcada como Pagada, sección blanca)
        $row1 = [
            'OD5F0358-B0D7', 'CAP811007MT7', 'XAXX010101000', 'I', 'J3932', '2026-07-16', '59530.75', '2152.67', '0', '61683.41', '01', 'Venta', 'OD5F0358-B0D7', 'https://verify', 'PUE',
            '#N/A', '#N/A', '#N/A', '#N/A', '#N/A', '#N/A', '#N/A', '#N/A', '', '#N/A'
        ];

        // Fila 2: Complemento de pago (Total principal $0.00 en J, pero total real en Y es $1,740.00, número complemento J3422, fecha 2026-01-30)
        $row2 = [
            '89C5E515-CEC0', 'CAP811007MT7', 'DSS110113G75', 'I', '#N/A', '2026-01-30', '0.00', '0.00', '0', '0.00', '01', '#N/A', '#N/A', 'https://verify', 'PPD',
            'OD5F0358-B0D7', 'CAP811007MT7', 'DSS110113G75', 'P', 'J3422', '2026-01-30', '1500.00', '240.00', '', '1740.00'
        ];

        $csvContent = implode(',', $headers) . "\n"
            . implode(',', $row1) . "\n"
            . implode(',', $row2) . "\n";

        $file = UploadedFile::fake()->createWithContent('invoices.csv', $csvContent);

        $service = new InvoiceImportService();
        $results = $service->import($file);

        $this->assertEquals(2, $results['imported']);
        $this->assertEquals(0, $results['duplicates']);

        // Validamos la factura normal (Sección blanca):
        $this->assertDatabaseHas('invoices', [
            'uuid' => 'OD5F0358-B0D7',
            'rfc_emisor' => 'CAP811007MT7',
            'rfc_receptor' => 'XAXX010101000',
            'numero_factura' => 'J3932',
            'fecha' => '2026-07-16',
            'total' => 61683.41,
            'tipo' => 'I',
            'status' => 'Pagado' // PUE = Pagado
        ]);

        // Validamos el complemento de pago (Sección durazno):
        $this->assertDatabaseHas('invoices', [
            'uuid' => '89C5E515-CEC0',
            'rfc_emisor' => 'CAP811007MT7',
            'rfc_receptor' => 'DSS110113G75',
            'numero_factura' => 'J3422', // Traído de la sección durazno
            'fecha' => '2026-01-30', // Traído de la sección durazno
            'total' => 1740.00, // Traído del segundo TOTAL (columna Y)
            'imp_pagado' => 1740.00,
            'uuid_relacionado' => 'OD5F0358-B0D7', // Relacionada a la anterior
            'tipo' => 'P',
            'status' => 'Pagado'
        ]);
    }
    public function test_invoice_controller_index_returns_invoices(): void
    {
        // 1. Crear algunas facturas en la base de datos
        Invoice::create([
            'uuid' => 'UUID-TEST-333',
            'rfc_emisor' => 'CAP811007MT7',
            'rfc_receptor' => 'XAXX010101000',
            'numero_factura' => 'J1001',
            'fecha' => '2026-08-31',
            'total' => 100.00,
            'tipo' => 'I',
            'status' => 'Pagado'
        ]);

        // 2. Hacer una petición GET a la ruta de facturas
        $response = $this->get(route('invoices.index'));

        // 3. Validar que responde con éxito (200)
        $response->assertStatus(200);

        // 4. Validar que la respuesta de Inertia contiene las facturas creadas
        $response->assertInertia(fn ($page) => $page
            ->component('Invoices/Index')
            ->has('invoices', 1)
            ->where('invoices.0.numero_factura', 'J1001')
            ->has('prefixes', 1)
            ->where('prefixes.0', 'J')
        );
    }

}



