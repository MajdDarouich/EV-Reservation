<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStationManagementRequest;
use App\Http\Requests\UpdateStationManagementRequest;
use App\Models\Station;
use App\Services\StationManagementService;
use Illuminate\Http\Request;

class StationManagementController extends Controller
{
    

    public function __construct(protected StationManagementService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $request->all();
        return $this->service->index($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStationManagementRequest $request)
    {
        $data = $request->validated();
        return $this->service->store($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStationManagementRequest $request, Station $station)
    {
        $data = $request->validated();
        return $this->service->update($data, $station->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Station $station)
    {
        return $this->service->destroy($station->id);
    }
}
