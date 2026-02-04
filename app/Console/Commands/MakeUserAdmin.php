<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin';
    protected $description = 'Make the first user an administrator';

    public function handle()
    {
        // Create permissions
        $permissions = ['ver vacaciones', 'administrar vacaciones', 'generar reportes', 'ver reportes'];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Create admin role
        $role = Role::firstOrCreate(['name' => 'Administrador']);
        $role->syncPermissions($permissions);

        // Get first user
        $user = User::first();

        if (!$user) {
            $this->error('❌ No hay usuarios en la base de datos');
            return 1;
        }

        // Assign role
        $user->assignRole('Administrador');

        $this->info("✅ Usuario {$user->email} es ahora Administrador");
        return 0;
    }
}
