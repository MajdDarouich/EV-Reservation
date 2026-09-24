<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RoleManagementService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
    

    public function __construct(protected RoleManagementService $Service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->Service->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();
        return $this->Service->store($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->validated();
        return $this->Service->update($data, $role->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        return $this->Service->destroy($role->id);
    }
}
