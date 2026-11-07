<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChooseChargerRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Services\Api\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(protected ReservationService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json($this->service->index());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request)
    {
        $data = $request->validated();

        return response()->json($this->service->store($data));
    }

    public function chooseCharger(ChooseChargerRequest $request)
    {
        $data = $request->validated();

        return response()->json($this->service->chooseCharger($data));
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $reservation)
    {
        return response()->json($this->service->cancel($reservation));
    }
}
