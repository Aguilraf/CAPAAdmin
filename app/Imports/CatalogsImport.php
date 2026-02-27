<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CatalogsImport implements WithMultipleSheets
{
    protected $selectedSheets;

    public function __construct(array $selectedSheets = [])
    {
        $this->selectedSheets = $selectedSheets;
    }

    public function sheets(): array
    {
        $allSheets = [
            'Empleados' => new EmpleadosImport(),
            'Puestos' => new PuestosImport(),
            'Organismos' => new OrganismosImport(),
            'Proveedores' => new ProvidersImport(),
            'Vehiculos' => new VehiclesImport(),
            'Tarifas_Viaticos' => new TravelAllowanceRatesImport(),
            'Materiales' => new MaterialesImport(),
            'Unidades_Medida' => new UnidadMedidaImport(),
            'Capitulos' => new CapitulosImport(),
            'Partidas' => new PartidasImport(),
            'Comunidades' => new CommunitiesImport(),
            'Bomberos' => new FirefightersImport(),
        ];

        if (empty($this->selectedSheets)) {
            return $allSheets;
        }

        // Return only the selected sheets that exist in our mapping
        return array_intersect_key($allSheets, array_flip($this->selectedSheets));
    }
}
