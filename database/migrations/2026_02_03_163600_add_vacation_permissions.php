<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear permiso
        $permiso = Permission::create(['name' => 'ver vacaciones']);

        // Asignar a roles existentes (si existen)
        $roles = Role::whereIn('name', ['Administrador', 'Empleado'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($permiso);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('name', 'ver vacaciones')->delete();
    }
};
