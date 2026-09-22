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
            'full_name' => 'Admin',
            'email' => 'admin@example.com',
            'phone_number' => '1234567890',
            'password' => Hash::make('password'),
        ]);
        
        Role::create(['name' => 'Super Admin']);
        $user->assignRole('Super Admin');

    }
}
