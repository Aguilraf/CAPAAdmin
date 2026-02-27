<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CatalogPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Empleados
            'ver empleados',
            'crear empleados',
            'editar empleados',
            'eliminar empleados',
            // Puestos
            'ver puestos',
            'crear puestos',
            'editar puestos',
            'eliminar puestos',
            // Organismos
            'ver organismos',
            'crear organismos',
            'editar organismos',
            'eliminar organismos',
            // Proveedores
            'ver proveedores',
            'crear proveedores',
            'editar proveedores',
            'eliminar proveedores',
            // Vehículos
            'ver vehiculos',
            'crear vehiculos',
            'editar vehiculos',
            'eliminar vehiculos',
            // Viáticos
            'ver viaticos',
            'crear viaticos',
            'editar viaticos',
            'eliminar viaticos',
            // Materiales
            'ver materiales',
            'crear materiales',
            'editar materiales',
            'eliminar materiales',
            // Unidades de Medida
            'ver unidades-medida',
            'crear unidades-medida',
            'editar unidades-medida',
            'eliminar unidades-medida',
            // Capítulos
            'ver capitulos',
            'crear capitulos',
            'editar capitulos',
            'eliminar capitulos',
            // Partidas
            'ver partidas',
            'crear partidas',
            'editar partidas',
            'eliminar partidas',
            // Leyendas
            'ver leyendas',
            'crear leyendas',
            'editar leyendas',
            'eliminar leyendas',
            // Importación/Exportación
            'importar datos',
            'exportar datos'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Asignar al administrador
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(Permission::all());
        }
    }
}
