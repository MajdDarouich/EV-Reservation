<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChargerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Chargers = [
            [
                'charging_type_id' => 1,
                'serial_number' => 'CHG-001',
                'manufacturer' => 'ChargeTech',
                'model' => 'CT-1000',
                'firmware_version' => '1.0.0',
                'power_kwh' => 22.0,
                'status' => 'Available',
            ],

            [
                'charging_type_id' => 2,
                'serial_number' => 'CHG-002',
                'manufacturer' => 'ChargeTech',
                'model' => 'CT-2000',
                'firmware_version' => '1.0.0',
                'power_kwh' => 50.0,
                'status' => 'Available',
            ],
        ];
    }
}
