<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::query();
            return DataTables::of($roles)
                ->addColumn('action', function ($role) {
                    return view('roles.action', compact('role'));
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('roles.index');
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name|max:255',
            'permissions' => 'array',
        ]);

        $role = new Role();
        $role->name = $request->name;
        $role->permissions = $request->permissions ?? [];
        $role->save();

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id . '|max:255',
            'permissions' => 'array',
        ]);

        $role->name = $request->name;
        $role->permissions = $request->permissions ?? [];
        $role->save();

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully');
    }
}
