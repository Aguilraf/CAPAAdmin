<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Empleado;
use App\Models\Material;
use App\Models\ReporteBitacora;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Barryvdh\DomPDF\Facade\Pdf;

class MaterialRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_material_request_pdf()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        $empleado = Empleado::factory()->create();
        $user->empleado_id = $empleado->id;
        $user->save();

        $material = Material::factory()->create([
            'articulo' => 'Test Material',
            'unidad_medida_id' => null, // Simplified for test
        ]);

        // 2. Mock PDF
        // We want to assert that a PDF download is returned.
        // Mocking the facade allows us to check if loadView was called, 
        // but can sometimes interfere with the actual download response returning.
        // For a basic integration test, we might just want to check the response headers.
        // However, let's try to spy on it if possible, or just assert the response is correct.

        // Let's rely on checking the DB side effects and response headers first.

        $postData = [
            'fecha' => now()->toDateString(),
            'destinatario_nombre' => 'Gerente Test',
            'destinatario_cargo' => 'Gerente General',
            'solicitante_nombre' => 'Solicitante Test',
            'solicitante_cargo' => 'Cargo Test',
            'solicitante_departamento' => 'Depto Test',
            'items' => [
                [
                    'material_id' => $material->id,
                    'cantidad' => 5,
                    'custom_articulo' => 'Test Material',
                    'custom_unidad' => 'Pieza'
                ]
            ]
        ];

        // 3. Act
        $response = $this->actingAs($user)
            ->post(route('reportes.material-request.print'), $postData);

        // 4. Assert
        $response->assertStatus(200);

        // Assert header is PDF
        $response->assertHeader('content-type', 'application/pdf');

        // Assert Bitacora created
        $this->assertDatabaseHas('reporte_bitacoras', [
            'user_id' => $user->id,
            'destinatario_nombre' => 'Gerente Test',
            'solicitante_nombre' => 'Solicitante Test',
        ]);

        // Verify JSON data in bitacora contains our items
        $bitacora = ReporteBitacora::latest()->first();
        $this->assertEquals('Test Material', $bitacora->materiales[0]['articulo']);
        $this->assertEquals(5, $bitacora->materiales[0]['cantidad']);
    }

    public function test_validation_works()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reportes.material-request.print'), []); // Empty data

        $response->assertSessionHasErrors(['fecha', 'destinatario_nombre', 'items']);
    }
}
