<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

            $resources = [
                'users',
                'stations',
                'chargers',
                'reservations',
                'sessions',
                'pricing',
                'payments',
                'tickets',
                'roles',
            ];

            $actions = ['view', 'create', 'update', 'delete'];

            foreach ($resources as $resource) {
                foreach ($actions as $action) {
                    Permission::firstOrCreate([
                        'name'       => "{$resource}.{$action}",
                        'guard_name' => 'web', 
                    ]);
                }
            }

            // reports only has "view"
            Permission::firstOrCreate([
                'name'       => 'reports.view',
                'guard_name' => 'web',
            ]);
        }
    }
