<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    // ── Index: roles matrix ───────────────────────────────────────
    public function index()
    {
        $roles       = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        // Group permissions by resource prefix (e.g. "sales.view" → "sales")
        $grouped = $permissions->groupBy(fn($p) => explode('.', $p->name)[0])->sortKeys();

        $users = User::with('roles')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles', 'permissions', 'grouped', 'users'));
    }

    // ── Save: toggle permissions on a role ────────────────────────
    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', "Permissions for role \"{$role->name}\" updated.");
    }

    // ── Create new role ───────────────────────────────────────────
    public function storeRole(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:roles,name']);
        Role::create(['name' => trim(strtolower($request->name)), 'guard_name' => 'web']);
        return back()->with('success', "Role \"{$request->name}\" created.");
    }

    // ── Delete role ───────────────────────────────────────────────
    public function destroyRole(Role $role)
    {
        if (in_array($role->name, ['admin', 'salesperson', 'accountant'])) {
            return back()->with('error', "Cannot delete a built-in role.");
        }
        $role->delete();
        return back()->with('success', "Role \"{$role->name}\" deleted.");
    }

    // ── Create new permission ─────────────────────────────────────
    public function storePermission(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:permissions,name']);
        Permission::create(['name' => trim(strtolower($request->name)), 'guard_name' => 'web']);
        return back()->with('success', "Permission \"{$request->name}\" created.");
    }

    // ── Assign role to user ───────────────────────────────────────
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles([$request->role]);

        return back()->with('success', "{$user->name} assigned to role \"{$request->role}\".");
    }
}
