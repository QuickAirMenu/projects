<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('name')->paginate(30);
        return view('users.permissions', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:permissions,name']);
        Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return redirect()->route('permissions.index')
            ->with('success', __('common.created_successfully'));
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.index')
            ->with('success', __('common.deleted_successfully'));
    }
}
