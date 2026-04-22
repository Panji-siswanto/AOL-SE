<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        // reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // permissions
        $permissions = [

            // registration
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
            Permission::create(['name' => $permission]);
        }

        // roles
        $admin = Role::create(['name' => 'admin']);
        $owner = Role::create(['name' => 'owner']);
        $renter = Role::create(['name' => 'renter']);

        // assign permissions

        // ADMIN → everything
        $admin->givePermissionTo(Permission::all());

        // OWNER
        $owner->givePermissionTo([
            'submit space registration',
            'view own space registrations',
            'manage own spaces',
            'view incoming rent requests',
            'respond rent request',
            'view rents',
            'manage own rents',
        ]);

        // RENTER
        $renter->givePermissionTo([
            'view spaces',
            'view space detail',
            'bookmark space',
            'remove bookmark',
            'view bookmarks',
            'create rent request',
            'view own rent requests',
            'send proposal',
            'send response',
            'view rents',
        ]);
    }
}
