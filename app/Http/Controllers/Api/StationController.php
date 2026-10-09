<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Services\Api\StationService;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function __construct(protected StationService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);
    
        return $this->service->index(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );
    }

    public function searchByAddress(Request $request)
    {

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['required', 'string'],
        ]);

        return $this->service->searchByAddress(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['address'],
        );
    }
}
