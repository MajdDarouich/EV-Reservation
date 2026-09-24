<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        $roles = Role::with('permissions')
        ->orderBy('name')
        ->get();

        $permissions = Permission::orderBy('name')->get();

        $groupedPermissions = $permissions->groupBy(function ($permission) {
        return explode('.', $permission->name)[0];
    });
    
        return view('content.role-management.index', compact('roles', 'permissions', 'groupedPermissions'));
    }

    public function store(array $data)
    {
        DB::beginTransaction();

        try{
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web'
            ]);

            if (!empty($data['permissions'])) {
                $permissions = Permission::whereIn(
                    'id', $data['permissions']
                )->get();

                $role->syncPermissions($permissions);
            }

            DB::commit();

            return back()->with('success', 'Role created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, int $id)
    {
        $role = Role::findOrFail($id);

        DB::beginTransaction();

        try {
            if($role->name === 'Super Admin' && $data['name'] !== 'Super Admin') {
                abort(403, 'Cannot change the name of the Super Admin role.');
            }

            $role->update([
                'name' => $data['name'],
            ]);

            $permissions = Permission::whereIn('id', $data['permissions'] ?? [])->get();

            $role->syncPermissions($permissions);

            DB::commit();

            return back()->with('success', 'Role updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'Super Admin') {
            abort(403, 'Cannot delete the Super Admin role.');
        }

        if( $role->users()->count() > 0) {
            abort(409, 'Cannot delete a role that is assigned to users.');
        }

        DB::beginTransaction();

        try {
            $role->delete();
            DB::commit();

            return back()->with('success', 'Role deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
