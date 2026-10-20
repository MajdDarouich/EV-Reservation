<?php

namespace Database\Seeders;

use App\Models\ChargingType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChargingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chargingTypes = [
            ['code' => 'AC22', 'current_type' => 'AC', 'power_kwh' => 22.0],
            ['code' => 'DC50', 'current_type' => 'DC', 'power_kwh' => 50.0],
            ['code' => 'DC100', 'current_type' => 'DC', 'power_kwh' => 100.0],
            ['code' => 'DC150', 'current_type' => 'DC', 'power_kwh' => 150.0],
            ['code' => 'DC250', 'current_type' => 'DC', 'power_kwh' => 250.0],
        ];

        foreach ($chargingTypes as $type) {
            ChargingType::updateOrCreate(
                ['code' => $type['code']],
                $type,
            );
        }
    }
}
