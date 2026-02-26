<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->latest()->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();

        $rolePerms = $role->permissions()
            ->pluck('permissions.id')
            ->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePerms'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()
            ->route('admin.system.roles.index')
            ->with('success', 'Permission role berhasil diperbarui.');
    }
}
