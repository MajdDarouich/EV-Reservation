<?php

namespace App\Services;

use App\Enums\ChargerStatus;
use App\Models\Charger;
use App\Models\Connector;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->with('chargers.connectors')
            ->orderBy('name')
            ->get();

        $selectedStation = $stations->firstWhere('id', $request->integer('station_id'));
        $availableConnectors = Connector::query()
            ->whereNull('charger_id')
            ->orderBy('code')
            ->orderBy('max_power_kw')
            ->get();
        $connectorInventory = Connector::query()
            ->orderBy('code')
            ->orderBy('max_power_kw')
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

        return view('content.charger-management.index', compact(
            'stations',
            'selectedStation',
            'availableChargers',
            'availableConnectors',
            'connectorInventory'
        ));
    }

    public function attach(array $data)
    {
        DB::beginTransaction();

        try {
            $station = Station::query()->findOrFail($data['station_id']);
            Charger::whereIn('id', $data['charger_ids'])
                ->update(['station_id' => $station->id]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $station->id])
            ->with('success', 'Chargers attached to the station successfully.');
    }

    public function detach(Charger $charger, array $data)
    {
        DB::beginTransaction();

        try {
            $charger->update(['station_id' => null]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $data['station_id']])
            ->with('success', 'Charger removed from the station successfully.');
    }

    public function update(Charger $charger, array $data)
    {
        DB::beginTransaction();

        try {
            $charger->update($data);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $charger->station_id])
            ->with('success', 'Charger updated successfully.');
    }

    public function markOutOfService(Charger $charger)
    {
        DB::beginTransaction();

        try {
            $charger->update(['status' => ChargerStatus::OUT_OF_SERVICE->value]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $charger->station_id])
            ->with('success', 'Charger marked as out of service.');
    }

    public function markAvailable(Charger $charger)
    {
        DB::beginTransaction();

        try {
            $charger->update(['status' => ChargerStatus::AVAILABLE->value]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $charger->station_id])
            ->with('success', 'Charger marked as available.');
    }
}
