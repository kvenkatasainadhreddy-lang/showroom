<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index() { return view('admin.users.index', ['users' => User::with('roles')->paginate(20)]); }
    public function create() { $roles = Role::all(); return view('admin.users.create', compact('roles')); }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|min:8|confirmed', 'role' => 'required|exists:roles,name']);
        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)]);
        $user->assignRole($request->role);
        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user) { $roles = Role::all(); return view('admin.users.edit', compact('user', 'roles')); }

    public function update(Request $request, User $user)
    {
        $request->validate(['name' => 'required', 'email' => 'required|email|unique:users,email,' . $user->id, 'role' => 'required|exists:roles,name']);
        $user->update(['name' => $request->name, 'email' => $request->email]);
        if ($request->filled('password')) { $user->update(['password' => Hash::make($request->password)]); }
        $user->syncRoles([$request->role]);
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user) { $user->delete(); return redirect()->route('admin.users.index')->with('success', 'User deleted.'); }
}
