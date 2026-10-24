<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DetachChargerRequest;
use App\Http\Requests\StoreChargerRequest;
use App\Http\Requests\UpdateChargerRequest;
use App\Models\Charger;
use App\Services\ChargerManagementService;
use Illuminate\Http\Request;

class ChargerManagementController extends Controller
{
    public function __construct(protected ChargerManagementService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function attach(StoreChargerRequest $request)
    {
        $data = $request->validated();

        return $this->service->attach($data);
    }

    public function detach(DetachChargerRequest $request, Charger $charger)
    {
        return $this->service->detach($charger, $request->validated());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChargerRequest $request, Charger $charger)
    {
        return $this->service->update($charger, $request->validated());
    }

    public function markOutOfService(Charger $charger)
    {
        return $this->service->markOutOfService($charger);
    }

    public function markAvailable(Charger $charger)
    {
        return $this->service->markAvailable($charger);
    }
}
