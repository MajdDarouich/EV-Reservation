<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttachVehicleRequest;
use App\Http\Requests\DetachVehicleRequest;
use App\Models\Vehicle;
use App\Services\Api\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(protected VehicleService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * Attach a new resource to the storage.
     */
    public function attach(AttachVehicleRequest $request)
    {
        $vehicle = $this->service->attach($request->validated());

        return response()->json([
            'message' => 'Vehicle attached successfully.',
            'vehicle' => $vehicle,
        ], 200);
    }

    public function detach(DetachVehicleRequest $request)
    {
        $vehicle = $this->service->detach($request->validated());

        return response()->json([
            'message' => 'Vehicle detached successfully.',
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Set the default vehicle.
     */
    public function makeDefault(DetachVehicleRequest $request)
    {
        $vehicle = $this->service->makeDefault($request->validated());

        return response()->json([
            'message' => 'Vehicle set as default successfully.',
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Remove the default status from a vehicle.
     */
    public function removeDefault(DetachVehicleRequest $request)
    {
        $vehicle = $this->service->removeDefault($request->validated());

        return response()->json([
            'message' => 'Vehicle removed as default successfully.',
            'vehicle' => $vehicle,
        ]);
    }
}
