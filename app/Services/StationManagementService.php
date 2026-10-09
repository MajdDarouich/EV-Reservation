<?php

namespace App\Services;

use App\Models\Station;
use App\Models\StationWorkingHour;
use Illuminate\Support\Facades\DB;

class StationManagementService
{
    public function index(array $data)
    {
        $search = $data['search'] ?? null;

        $filtered = fn () => Station::query()->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        });

        $stations = $filtered()
            ->with('stationWorkingHours')
            ->latest()
            ->paginate(10);

        $mapStations = $filtered()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'address', 'city', 'status', 'latitude', 'longitude', 'avg_rating'])
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'address' => $s->address,
                'city' => $s->city,
                'status' => $s->status->value,
                'lat' => (float) $s->latitude,
                'lng' => (float) $s->longitude,
                'rating' => number_format((float) $s->avg_rating, 2),
            ]);

        return view('content.station-management.index', compact('stations', 'mapStations'));
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            $station = Station::create($this->extractStationData($data));
            $this->syncWorkingHours($station, $data['working_hours'] ?? []);

            DB::commit();

            return back()->with('success', 'Station created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, int $stationId)
    {
        try {
            DB::beginTransaction();

            $station = Station::findOrFail($stationId);
            $station->fill($this->extractStationData($data));
            $station->save();

            if (array_key_exists('working_hours', $data)) {
                $this->syncWorkingHours($station, $data['working_hours']);
            }

            DB::commit();

            return back()->with('success', 'Station updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(int $stationId)
    {
        try {
            DB::beginTransaction();

            $station = Station::findOrFail($stationId);
            $station->stationWorkingHours()->delete();
            $station->delete();

            DB::commit();

            return back()->with('success', 'Station deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function extractStationData(array $data): array
    {
        $fields = [
            'name',
            'description',
            'address',
            'city',
            'country',
            'latitude',
            'longitude',
            'facilities',
            'contact_info',
            'status',
            'default_grace_period_minutes',
            'default_overstay_fee_amount',
            'default_overstay_interval_minutes',
            'cancellation_window_minutes',
            'no_show_period_days',
            'avg_rating',
        ];

        $stationData = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $stationData[$field] = $data[$field];
            }
        }

        return $stationData;
    }

    protected function syncWorkingHours(Station $station, array $workingHours): void
    {
        $station->stationWorkingHours()->delete();

        foreach ($workingHours as $day => $hours) {
            if (! is_array($hours)) {
                continue;
            }

            $dayOfWeek = (int) $day;

            if ($dayOfWeek < 0 || $dayOfWeek > 6) {
                continue;
            }

            $opensAt = $hours['opens_at'] ?? null;
            $closesAt = $hours['closes_at'] ?? null;
            $isOpened = isset($hours['is_opened']) ? filter_var($hours['is_opened'], FILTER_VALIDATE_BOOLEAN) : false;

            $opensAt = trim((string) ($opensAt ?? '')) === '' ? null : $opensAt;
            $closesAt = trim((string) ($closesAt ?? '')) === '' ? null : $closesAt;

            if ($isOpened && ($opensAt === null || $closesAt === null)) {
                continue;
            }

            if (! $isOpened) {
                $opensAt = $opensAt ?? '00:00';
                $closesAt = $closesAt ?? '00:00';
            }

            StationWorkingHour::create([
                'station_id' => $station->id,
                'day_of_week' => $dayOfWeek,
                'opens_at' => $opensAt,
                'closes_at' => $closesAt,
                'is_opened' => $isOpened,
            ]);
        }
    }
}
