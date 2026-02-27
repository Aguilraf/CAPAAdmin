<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CatalogsExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Empleados' => new EmpleadoExport(),
            'Puestos' => new PuestosExport(),
            'Organismos' => new OrganismosExport(),
            'Proveedores' => new ProvidersExport(),
            'Vehiculos' => new VehiclesExport(),
            'Tarifas_Viaticos' => new TravelAllowanceRatesExport(),
            'Materiales' => new MaterialesExport(),
            'Unidades_Medida' => new UnidadMedidaExport(),
            'Capitulos' => new CapitulosExport(),
            'Partidas' => new PartidasExport(),
            'Comunidades' => new CommunitiesExport(),
            'Bomberos' => new FirefightersExport(),
        ];
    }
}
