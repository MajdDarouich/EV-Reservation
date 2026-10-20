<?php

namespace Database\Seeders;

use Database\Seeders\ChargerSeeder;
use Database\Seeders\ChargingTypeSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Client;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            PermissionSeeder::class,
            SuperAdminSeeder::class,
            RoleSeeder::class,
            ChargingTypeSeeder::class,
            ChargerSeeder::class,
        ]);

        $hasPersonalAccessClient = Client::query()
            ->where('provider', 'users')
            ->get()
            ->contains(fn (Client $client): bool => in_array('personal_access', $client->grant_types, true));

        if (! $hasPersonalAccessClient) {
            Artisan::call('passport:client', [
                '--personal' => true,
                '--provider' => 'users',
                '--name' => 'EV Reservation Personal Access Client',
                '--no-interaction' => true,
            ]);
        }
    }
}
