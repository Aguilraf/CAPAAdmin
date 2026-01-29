<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FirefightersPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos de Bomberos
        $permissions = [
            // Captura
            'capturar bomberos' => 'Permite registrar capturas mensuales de bomberos',

            // Recepción
            'recibir bomberos' => 'Permite gestionar y asignar capturas a requerimientos',

            // Reportes
            'reportes bomberos' => 'Permite generar y descargar reportes PDF de bomberos',

            // Comunidades
            'ver comunidades' => 'Permite ver el catálogo de comunidades',
            'crear comunidades' => 'Permite crear nuevas comunidades',
            'editar comunidades' => 'Permite editar comunidades existentes',
            'eliminar comunidades' => 'Permite eliminar comunidades',

            // Bomberos
            'ver bomberos' => 'Permite ver el catálogo de bomberos',
            'crear bomberos' => 'Permite crear nuevos bomberos',
            'editar bomberos' => 'Permite editar bomberos existentes',
            'eliminar bomberos' => 'Permite eliminar bomberos',

            // Configuración
            'configurar bomberos' => 'Permite configurar parámetros y diseño de reportes',

            // Importación
            'importar bomberos' => 'Permite importar capturas masivamente desde Excel',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Asignar todos los permisos al rol Administrador
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
        }

        $this->command->info('✅ Permisos de Bomberos creados exitosamente');
        $this->command->info('📊 Total de permisos creados: ' . count($permissions));
    }
}
