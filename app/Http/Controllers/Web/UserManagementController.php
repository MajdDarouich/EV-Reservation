<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserManagement;
use App\Http\Requests\UpdateUserManagement;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    
    public function __construct(private UserManagementService $service)
    {
    }
    

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);
        $roles = Role::all();
        return view('content.user-management.index', compact('users', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserManagement $request)
    {
        $data = $request->validated();
        return $this->service->storeUser($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserManagement $request, User $user)
    {
        $data = $request->validated();
        return $this->service->updateUser($user->id, $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        return $this->service->deleteUser($user->id);
    }
}
