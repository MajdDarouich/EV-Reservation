<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConnectorRequest;
use App\Http\Requests\UpdateConnectorRequest;
use App\Models\Charger;
use App\Models\Connector;
use App\Services\ConnectorManagementService;

class ConnectorManagementController extends Controller
{
    public function __construct(protected ConnectorManagementService $service)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConnectorRequest $request, Charger $charger)
    {
        return $this->service->store($charger, $request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Connector $connector)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Connector $connector)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConnectorRequest $request, Connector $connector)
    {
        return $this->service->update($connector, $request->validated());
    }

    public function markOutOfService(Connector $connector)
    {
        return $this->service->markOutOfService($connector);
    }

    public function markAvailable(Connector $connector)
    {
        return $this->service->markAvailable($connector);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Connector $connector)
    {
        //
    }
}
