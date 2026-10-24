<?php

namespace App\Services;

use App\Enums\ConnectorStatus;
use App\Models\Charger;
use App\Models\Connector;
use Illuminate\Support\Facades\DB;

class ConnectorManagementService
{
    /**
     * Create a new class instance.
     */
    public function store(Charger $charger, array $data)
    {
        DB::beginTransaction();

        try {
            Connector::query()
                ->whereKey($data['connector_id'])
                ->whereNull('charger_id')
                ->where('current_type', $charger->current_type)
                ->firstOrFail()
                ->update([
                    'charger_id' => $charger->id,
                    'number' => $data['number'],
                ]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $charger->station_id])
            ->with('success', 'Connector assigned successfully.');
    }

    public function update(Connector $connector, array $data)
    {
        DB::beginTransaction();

        try {
            $connector->update($data);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $connector->charger?->station_id])
            ->with('success', 'Connector updated successfully.');
    }

    public function markOutOfService(Connector $connector)
    {
        DB::beginTransaction();

        try {
            $connector->update(['status' => ConnectorStatus::OUT_OF_SERVICE->value]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $connector->charger?->station_id])
            ->with('success', 'Connector marked as out of service.');
    }

    public function markAvailable(Connector $connector)
    {
        DB::beginTransaction();

        try {
            $connector->update(['status' => ConnectorStatus::AVAILABLE->value]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return redirect()
            ->route('chargers.index', ['station_id' => $connector->charger?->station_id])
            ->with('success', 'Connector marked as available.');
    }
}
