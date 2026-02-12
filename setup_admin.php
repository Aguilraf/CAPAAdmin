<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== CONFIGURANDO USUARIO ADMINISTRADOR ===\n\n";

// 1. Buscar usuario
$user = User::where('username', 'aguilraf')->first();

if (!$user) {
    echo "✗ Usuario 'aguilraf' no encontrado\n";
    exit(1);
}

echo "✓ Usuario encontrado: {$user->name}\n";

// 2. Crear rol de administrador si no existe
$adminRole = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
echo "✓ Rol 'Administrador' verificado\n";

// 3. Crear permisos básicos si no existen
$permissions = [
    'ver dashboard',
    'gestionar usuarios',
    'gestionar empleados',
    'gestionar requerimientos',
    'gestionar reportes',
    'gestionar catálogos',
    'gestionar viáticos',
    'gestionar bomberos',
    'ver todo'
];

foreach ($permissions as $permissionName) {
    Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
}

echo "✓ Permisos básicos verificados\n";

// 4. Asignar todos los permisos al rol administrador
$adminRole->syncPermissions(Permission::all());
echo "✓ Permisos asignados al rol Administrador\n";

// 5. Asignar rol de administrador al usuario
if (!$user->hasRole('Administrador')) {
    $user->assignRole('Administrador');
    echo "✓ Rol 'Administrador' asignado a {$user->name}\n";
} else {
    echo "✓ Usuario ya tiene rol 'Administrador'\n";
}

// 6. Verificar permisos
echo "\n=== VERIFICACIÓN ===\n";
echo "Usuario: {$user->name}\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
echo "Permisos: " . $user->getAllPermissions()->count() . " permisos\n";

echo "\n✅ Configuración completada. El usuario es ahora administrador.\n";
