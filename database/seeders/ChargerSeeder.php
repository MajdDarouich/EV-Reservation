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
                'code' => 'ABB-DC-001',
                'manufacturer' => 'ABB',
                'model' => 'Terra 54',
                'current_type' => 'DC',
                'power_kw' => 50.0,
                'serial_number' => 'ABB-DC-001',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'code' => 'ABB-DC-002',
                'manufacturer' => 'ABB',
                'model' => 'Terra 94',
                'current_type' => 'DC',
                'power_kw' => 100.0,
                'serial_number' => 'ABB-DC-002',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'code' => 'ABB-DC-003',
                'manufacturer' => 'ABB',
                'model' => 'Terra 154',
                'current_type' => 'DC',
                'power_kw' => 150.0,
                'serial_number' => 'ABB-DC-003',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'code' => 'ABB-DC-004',
                'manufacturer' => 'ABB',
                'model' => 'Terra 254',
                'current_type' => 'DC',
                'power_kw' => 250.0,
                'serial_number' => 'ABB-DC-004',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'code' => 'ABB-DC-001',
                'manufacturer' => 'ABB',
                'model' => 'Terra 54',
                'current_type' => 'DC',
                'power_kw' => 50.0,
                'serial_number' => 'ABB-CHA-001',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
            [
                'code' => 'ABB-DC-002',
                'manufacturer' => 'ABB',
                'model' => 'Terra 94',
                'current_type' => 'DC',
                'power_kw' => 100.0,
                'serial_number' => 'ABB-CHA-002',
                'firmware_version' => '2.0.0',
                'status' => 'Available',
            ],
        ];

        Charger::where('current_type', 'AC')->delete();

        foreach ($chargers as $charger) {
            Charger::updateOrCreate(
                ['serial_number' => $charger['serial_number']],
                $charger + ['station_id' => null],
            );
        }
    }
}
