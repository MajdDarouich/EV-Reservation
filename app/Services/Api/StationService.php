<?php

namespace App\Services\Api;

use App\Models\Station;

class StationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function index(float $lat, float $lon)
    {
        $stations = Station::with('StationWorkingHours')->get();

        foreach ($stations as $station) {
            $station->distance_km = round($this->calculateDistance($station->latitude, $station->longitude, $lat, $lon), 2);
        }

        return [
            'status' => 200,
            'message' => 'Station list',
            'data' => $stations
        ];
    }

    public function searchByAddress(float $lat, float $lon, string $address)
    {
        $stations = Station::where('address', 'like', '%' . $address . '%')->get();

        foreach ($stations as $station) {
            $station->distance_km = round($this->calculateDistance($station->latitude, $station->longitude, $lat, $lon), 2);
        }

        return [
            'status' => 200,
            'message' => 'Search results',
            'data' => $stations
        ];
    }

    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        // Convert latitude and longitude from degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Calculate the differences between the latitudes and longitudes
        $latDiff = $lat2Rad - $lat1Rad;
        $lonDiff = $lon2Rad - $lon1Rad;

        // Apply the Haversine formula
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Calculate the distance in kilometers
        return $earthRadius * $c;
    }

}
