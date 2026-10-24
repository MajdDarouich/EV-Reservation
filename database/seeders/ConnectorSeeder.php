<?php

namespace Database\Seeders;

use App\Models\Connector;
use Illuminate\Database\Seeder;

class ConnectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $connectors = [
            ['charger_id' => null, 'code' => 'Type2', 'current_type' => 'AC', 'number' => null, 'max_power_kw' => 22.0],
            ['charger_id' => null, 'code' => 'CCS2', 'current_type' => 'DC', 'number' => null, 'max_power_kw' => 50.0],
            ['charger_id' => null, 'code' => 'CCS2', 'current_type' => 'DC', 'number' => null, 'max_power_kw' => 100.0],
            ['charger_id' => null, 'code' => 'CCS2', 'current_type' => 'DC', 'number' => null, 'max_power_kw' => 150.0],
            ['charger_id' => null, 'code' => 'CCS2', 'current_type' => 'DC', 'number' => null, 'max_power_kw' => 250.0],
            ['charger_id' => null, 'code' => 'CHAdeMO', 'current_type' => 'DC', 'number' => null, 'max_power_kw' => 50.0],
            ['charger_id' => null, 'code' => 'CHAdeMO', 'current_type' => 'DC', 'number' => null, 'max_power_kw' => 100.0],
        ];

        foreach ($connectors as $connector) {
            Connector::updateOrCreate(
                [
                    'code' => $connector['code'],
                    'current_type' => $connector['current_type'],
                    'max_power_kw' => $connector['max_power_kw'],
                ],
                $connector
            );
        }
    }
}
