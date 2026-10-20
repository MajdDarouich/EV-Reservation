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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Charger $charger)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Charger $charger)
    {
        //
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
