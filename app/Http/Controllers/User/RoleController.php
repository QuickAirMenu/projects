<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles       = Role::withCount('users')->get();
        $permissions = Permission::orderBy('name')->get();
        return view('users.roles', compact('roles', 'permissions'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('users.roles-create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', __('common.created_successfully'));
    }

    public function edit(Role $role)
    {
        $permissions        = Permission::orderBy('name')->get();
        $rolePermissionIds  = $role->permissions->pluck('id')->toArray();
        return view('users.roles-edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name,' . $role->id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', __('common.updated_successfully'));
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['super-admin', 'admin'])) {
            return redirect()->route('roles.index')
                ->with('error', __('messages.cannot_delete_system_role'));
        }

        $role->delete();
        return redirect()->route('roles.index')
            ->with('success', __('common.deleted_successfully'));
    }
}
