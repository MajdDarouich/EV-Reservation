<?php

namespace Database\Seeders;

use App\Models\Charger;
use Illuminate\Database\Seeder;

class ChargerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chargers = [
            [
                'charging_type_id' => 1,
                'serial_number' => 'ABB-AC-003',
                'manufacturer' => 'ABB',
                'model' => 'Terra AC',
                'firmware_version' => '1.0.0',
                'status' => 'Available',
            ],

            [
                'charging_type_id' => 2,
                'serial_number' => 'ABB-DC-001',
                'manufacturer' => 'ABB',
                'model' => 'Terra 54',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'charging_type_id' => 3,
                'serial_number' => 'ABB-DC-002',
                'manufacturer' => 'ABB',
                'model' => 'Terra 100',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'charging_type_id' => 4,
                'serial_number' => 'ABB-DC-003',
                'manufacturer' => 'ABB',
                'model' => 'Terra 150',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'charging_type_id' => 5,
                'serial_number' => 'ABB-DC-004',
                'manufacturer' => 'ABB',
                'model' => 'Terra 250',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'charging_type_id' => 2,
                'serial_number' => 'ABB-CHA-001',
                'manufacturer' => 'ABB',
                'model' => 'Terra 54',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'charging_type_id' => 3,
                'serial_number' => 'ABB-CHA-002',
                'manufacturer' => 'ABB',
                'model' => 'Terra 100',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
        ];

        foreach ($chargers as $charger) {
            Charger::updateOrCreate(
                ['serial_number' => $charger['serial_number']],
                [
                    ...$charger,
                    'station_id' => null,
                ],
            );
        }
    }
}
