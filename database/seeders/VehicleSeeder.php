<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'user_id' => null,
                'vin' => '5YJ3E1EA1KF000001',
                'license_plate' => 'EV-001',
                'manufacturer' => 'Tesla',
                'model' => 'Model 3',
                'model_year' => 2025,
                'battery_capacity_kwh' => 75.0,
                'ac_max_kw' => 11.0,
                'dc_max_kw' => 250.0,
                'connector_type' => 'CCS2'
            ],
            [
                'user_id' => null,
                'vin' => '7SAYGDEE1NF000002',
                'license_plate' => 'EV-002',
                'manufacturer' => 'Tesla',
                'model' => 'Model Y',
                'model_year' => 2025,
                'battery_capacity_kwh' => 75.0,
                'ac_max_kw' => 11.0,
                'dc_max_kw' => 250.0,
                'connector_type' => 'CCS2'
            ],
            [
                'user_id' => null,
                'vin' => 'WVGZZZ5NZNW000003',
                'license_plate' => 'EV-003',
                'manufacturer' => 'Volkswagen',
                'model' => 'ID.4',
                'model_year' => 2025,
                'battery_capacity_kwh' => 77.0,
                'ac_max_kw' => 11.0,
                'dc_max_kw' => 135.0,
                'connector_type' => 'CCS2'
            ],
            [
                'user_id' => null,
                'vin' => 'KM8KNDAF1SU000004',
                'license_plate' => 'EV-004',
                'manufacturer' => 'Hyundai',
                'model' => 'Ioniq 5',
                'model_year' => 2025,
                'battery_capacity_kwh' => 84.0,
                'ac_max_kw' => 11.0,
                'dc_max_kw' => 235.0,
                'connector_type' => 'CCS2'
            ],
            [
                'user_id' => null,
                'vin' => 'KNDC3DLC5S5000005',
                'license_plate' => 'EV-005',
                'manufacturer' => 'Kia',
                'model' => 'EV6',
                'model_year' => 2025,
                'battery_capacity_kwh' => 84.0,
                'ac_max_kw' => 11.0,
                'dc_max_kw' => 240.0,
                'connector_type' => 'CCS2'
            ],
            [
                'user_id' => null,
                'vin' => 'WBY73AW07SF000006',
                'license_plate' => 'EV-006',
                'manufacturer' => 'BMW',
                'model' => 'i4',
                'model_year' => 2025,
                'battery_capacity_kwh' => 84.0,
                'ac_max_kw' => 11.0,
                'dc_max_kw' => 205.0,
                'connector_type' => 'CCS2'
            ],
            [
                'user_id' => null,
                'vin' => 'W1KCG2EB8SA000007',
                'license_plate' => 'EV-007',
                'manufacturer' => 'Mercedes-Benz',
                'model' => 'EQE',
                'model_year' => 2025,
                'battery_capacity_kwh' => 90.0,
                'ac_max_kw' => 11.0,
                'dc_max_kw' => 170.0,
                'connector_type' => 'CCS2'
            ],
            [
                'user_id' => null,
                'vin' => 'WAUZZZFZ5SA000008',
                'license_plate' => 'EV-008',
                'manufacturer' => 'Audi',
                'model' => 'Q4 e-tron',
                'model_year' => 2025,
                'battery_capacity_kwh' => 82.0,
                'ac_max_kw' => 11.0,
                'dc_max_kw' => 135.0,
                'connector_type' => 'CCS2'
            ],
            [
                'user_id' => null,
                'vin' => 'JN1AZ0CP9CT000009',
                'license_plate' => 'EV-009',
                'manufacturer' => 'Nissan',
                'model' => 'Leaf',
                'model_year' => 2024,
                'battery_capacity_kwh' => 40.0,
                'ac_max_kw' => 6.6,
                'dc_max_kw' => 46.0,
                'connector_type' => 'CHAdeMO'
            ],
            [
                'user_id' => null,
                'vin' => 'JA4J24A50NZ000010',
                'license_plate' => 'EV-010',
                'manufacturer' => 'Mitsubishi',
                'model' => 'Outlander PHEV',
                'model_year' => 2024,
                'battery_capacity_kwh' => 20.0,
                'ac_max_kw' => 3.7,
                'dc_max_kw' => 22.0,
                'connector_type' => 'CHAdeMO'
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['vin' => $vehicle['vin']],
                $vehicle
            );
        }
    }
}
