<?php

namespace App\Services;

use App\Enums\ChargerStatus;
use App\Models\Charger;
use App\Models\ChargingType;
use App\Models\Station;
use Illuminate\Http\Request;

class ChargerManagementService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function index(Request $request)
    {
        $stations = Station::query()
            ->with('chargers.chargingType')
            ->orderBy('name')
            ->get();

        $selectedStation = $stations->firstWhere('id', $request->integer('station_id'));
        $chargingTypes = ChargingType::query()
            ->orderBy('code')
            ->get();
        $availableChargers = Charger::query()
            ->when(
                $selectedStation,
                fn ($query) => $query->where(function ($query) use ($selectedStation) {
                    $query->whereNull('station_id')
                        ->orWhere('station_id', '!=', $selectedStation->id);
                }),
                fn ($query) => $query->whereNull('station_id')
            )
            ->orderBy('serial_number')
            ->get();

        return view('content.charger-management.index', compact('stations', 'selectedStation', 'availableChargers', 'chargingTypes'));
    }

    public function attach(array $data)
    {
        $station = Station::query()->findOrFail($data['station_id']);
        Charger::whereIn('id', $data['charger_ids'])
            ->update(['station_id' => $station->id]);

        return redirect()
            ->route('chargers.index', ['station_id' => $station->id])
            ->with('success', 'Chargers attached to the station successfully.');
    }

    public function detach(Charger $charger, array $data)
    {
        $charger->update(['station_id' => null]);

        return redirect()
            ->route('chargers.index', ['station_id' => $data['station_id']])
            ->with('success', 'Charger removed from the station successfully.');
    }

    public function update(Charger $charger, array $data)
    {
        $charger->update($data);

        return redirect()
            ->route('chargers.index', ['station_id' => $charger->station_id])
            ->with('success', 'Charger updated successfully.');
    }

    public function markOutOfService(Charger $charger)
    {
        $charger->update(['status' => ChargerStatus::OUT_OF_SERVICE->value]);

        return redirect()
            ->route('chargers.index', ['station_id' => $charger->station_id])
            ->with('success', 'Charger marked as out of service.');
    }

    public function markAvailable(Charger $charger)
    {
        $charger->update(['status' => ChargerStatus::AVAILABLE->value]);

        return redirect()
            ->route('chargers.index', ['station_id' => $charger->station_id])
            ->with('success', 'Charger marked as available.');
    }
}
