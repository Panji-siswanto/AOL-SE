<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Complete list of system permissions
        $permissions = [
            // space registration
            'submit space registration',
            'view own space registrations',
            'review space registrations',
            'approve space registration',
            'reject space registration',

            // spaces      
            'view spaces',
            'view space detail',
            'manage own spaces',

            // bookmark
            'bookmark space',
            'remove bookmark',
            'view bookmarks',

            // rent request
            'create rent request',
            'view own rent requests',
            'view incoming rent requests',
            'respond rent request',

            // messages
            'send proposal',
            'send response',

            // rent
            'view rents',
            'manage own rents',

            // admin
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Scaffold base roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $renter = Role::firstOrCreate(['name' => 'renter']);

  

        // ADMIN → Full platform access
        $admin->givePermissionTo(Permission::all());

        // OWNER → Full catalog management and rental fulfillment workflows
        $owner->givePermissionTo([
            'submit space registration',
            'view own space registrations',
            'manage own spaces',
            'view incoming rent requests',
            'respond rent request',
            'view rents',
            'manage own rents',
        ]);

        // RENTER (Base State) → Strictly limited to space discovery and dashboard monitoring
        // Sensitive writing actions are removed from this default group.
        $renter->givePermissionTo([
            'submit space registration',   
            'view spaces',
            'view space detail',
            'bookmark space',
            'remove bookmark',
            'view bookmarks',
            'view own space registrations', 
            'view own rent requests',       
            'view rents',
        ]);
    }
}