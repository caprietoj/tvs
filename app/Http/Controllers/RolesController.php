<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;        // Using Spatie Role model
use Spatie\Permission\Models\Permission;  // Using Spatie Permission model

class RolesController extends Controller
{
    public function index()
    {
        // Retrieve roles from Spatie's Role model
        $roles = Role::all();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        // Fetch available permissions seeded by RolesAndPermissionsSeeder using Spatie's Permission model
        $availablePermissions = Permission::pluck('name')->toArray();
        return view('admin.roles.create', compact('availablePermissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'permissions' => 'required|array',
        ]);

        // Create role and assign permissions using Spatie's methods
        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions']);

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        // Retrieve role's assigned permissions as an array
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $availablePermissions = Permission::pluck('name')->toArray();
        return view('admin.roles.edit', compact('role', 'rolePermissions', 'availablePermissions'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'permissions' => 'required|array',
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions']);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente');
    }

    // ...unchanged code...
}
