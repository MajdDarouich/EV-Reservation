<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartChargingRequest;
use App\Http\Requests\StopChargingRequest;
use App\Models\Reservation;
use App\Services\Api\ReservationLifecycleService;
use Illuminate\Http\Request;

class ChargingSessionManagementController extends Controller
{
    public function __construct(protected ReservationLifecycleService $service) {}

    public function index(Request $request)
    {
        $reservations = Reservation::query()
            ->with(['user', 'vehicle', 'connector.charger.station', 'chargingSession'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('date')
            ->latest('start_time')
            ->paginate(20)
            ->withQueryString();

        return view('content.charging-session-management.index', compact('reservations'));
    }

    public function start(StartChargingRequest $request, Reservation $reservation)
    {
        $this->service->startCharging($reservation->id);

        return redirect()
            ->route('charging-sessions.index')
            ->with('success', 'Charging session started successfully.');
    }

    public function cancel(Reservation $reservation)
    {
        $this->service->cancelByStaff($reservation->id);

        return redirect()
            ->route('charging-sessions.index')
            ->with('success', 'Reservation cancelled successfully.');
    }

    public function stop(StopChargingRequest $request, Reservation $reservation)
    {
        $this->service->stopCharging($reservation->id, $request->validated('reason'));

        return redirect()
            ->route('charging-sessions.index')
            ->with('success', 'Charging session stopped successfully.');
    }
}
