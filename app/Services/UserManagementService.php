<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserManagementService
{
    public function storeUser(array $data)
    {
        $role = $data['role'];
        unset($data['role']);

        if ($role === 'Super Admin') {
            abort(403, 'Cannot create a Super Admin user.');
        }

        DB::beginTransaction();

        try {
            $user = User::create($data);
            $user->assignRole($role);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return back()->with('success', 'User created successfully.');
    }

    public function updateUser(int $id, array $data)
    {
        $user = User::findOrFail($id);
        $role = $data['role'] ?? null;
        unset($data['role']);

        if ($user->hasRole('Super Admin') && $role !== null && $role !== 'Super Admin') {
            abort(403, 'Cannot change the role of a Super Admin user.');
        }

        if ($role === 'Super Admin' && ! $user->hasRole('Super Admin')) {
            abort(403, 'Cannot assign the Super Admin role.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::beginTransaction();

        try {
            $user->update($data);

            if ($role !== null) {
                $user->syncRoles($role);
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return back()->with('success', 'User updated successfully.');
    }

    public function deleteUser(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('Super Admin')) {
            abort(403, 'Cannot delete a Super Admin user.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
    
}
