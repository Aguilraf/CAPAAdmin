<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions safely
        $permissions = [
            'ver vacaciones',
            'administrar vacaciones',
            'aprobar vacaciones',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Give all to Admin
        $role = Role::where('name', 'Administrador')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'ver vacaciones',
            'administrar vacaciones',
            'aprobar vacaciones',
        ];

        foreach ($permissions as $permission) {
            $p = Permission::where('name', $permission)->first();
            if ($p) {
                $p->delete();
            }
        }
    }
};
