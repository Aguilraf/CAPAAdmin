use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create permissions
$permissions = ['ver vacaciones', 'administrar vacaciones', 'generar reportes', 'ver reportes'];
foreach($permissions as $perm) {
Permission::firstOrCreate(['name' => $perm]);
}

// Create admin role
$role = Role::firstOrCreate(['name' => 'Administrador']);
$role->syncPermissions($permissions);

// Get first user
$user = User::first();

if (!$user) {
echo "❌ No hay usuarios en la base de datos\n";
exit;
}

// Assign role
$user->assignRole('Administrador');

echo "✅ Usuario {$user->email} es ahora Administrador\n";