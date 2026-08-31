<?php

namespace Tests\Feature;

use Tests\TestCase;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class InvoiceImportTest extends TestCase
{
    public function test_read_real_excel_file(): void
    {
        $path = 'g:/Mi unidad/CAPA/DIR GEN/INGRESOS/Files/012026 XML ingresos al 31012026 - copia 3.xlsx';
        
        $this->assertTrue(file_exists($path), "El archivo de Excel no existe.");

        $sheets = Excel::toArray(new class implements ToCollection {
            public function collection(Collection $collection) {}
        }, $path);

        $data = $sheets[0];
        
        // Escribir en un archivo para que Cline lo lea de forma segura
        file_put_contents('tests_output_headers.txt', print_r($data[0], true) . PHP_EOL . print_r($data[1] ?? [], true));

        $this->assertNotEmpty($data);
    }
}

