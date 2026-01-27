<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $roles = Role::all();
        $employees = \App\Models\Empleado::activos()->get(['id', 'nombre']);
        return Inertia::render('Users/Form', [
            'roles' => $roles,
            'employees' => $employees
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name',
            'empleado_id' => 'required|exists:empleados,id'
        ]);

        $employee = \App\Models\Empleado::find($validated['empleado_id']);

        $user = User::create([
            'name' => $employee->nombre,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'empleado_id' => $validated['empleado_id'],
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        $employees = \App\Models\Empleado::activos()->get(['id', 'nombre']);

        return Inertia::render('Users/Form', [
            'user' => $user,
            'roles' => $roles,
            'employees' => $employees,
            'currentRole' => $user->roles->first()?->name
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'empleado_id' => 'required|exists:empleados,id'
        ]);

        $employee = \App\Models\Empleado::find($validated['empleado_id']);

        $user->name = $employee->nombre;
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->empleado_id = $validated['empleado_id'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
