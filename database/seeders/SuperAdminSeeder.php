<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'full_name' => 'Majd Darouich',
            'email' => 'majddarouich2005@gmail.com',
            'phone_number' => '0998413827',
            'password' => Hash::make('password'),
        ]);
        
        Role::create(['name' => 'Super Admin']);
        $user->assignRole('Super Admin');

        $user->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'stations.view',
            'stations.create',
            'stations.update',
            'stations.delete',
            'chargers.view',
            'chargers.create',
            'chargers.update',
            'chargers.delete',
            'reservations.view',
            'reservations.create',
            'reservations.update',
            'reservations.delete',
            'sessions.view',
            'sessions.create',
            'sessions.update',
            'sessions.delete',
            'pricing.view',
            'pricing.create',
            'pricing.update',
            'pricing.delete',
            'payments.view',
            'payments.create',
            'payments.update',
            'payments.delete',
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
        ]);

    }
}
