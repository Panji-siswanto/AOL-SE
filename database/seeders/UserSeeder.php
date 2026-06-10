<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('pass123'); 
        $verifiedStatus = Status::where('code', 'usr_verified')->value('id');
        $unverifiedStatus = Status::where('code', 'usr_unverified')->value('id');

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'renter']);
        Role::firstOrCreate(['name' => 'owner']);

        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@lapak.in'],
            ['username' => 'admin', 'name' => 'System Administrator', 'password' => $defaultPassword, 'ver_status' => $verifiedStatus, 'email_verified_at' => now()]
        );
        if (!$admin->hasRole('admin')) $admin->assignRole('admin');

        // 2. Renter
        $renter = User::firstOrCreate(
            ['email' => 'renter@lapak.in'],
            ['username' => 'renter', 'name' => 'Mas Renter', 'password' => $defaultPassword, 'ver_status' => $verifiedStatus, 'email_verified_at' => now()]
        );
        if (!$renter->hasRole('renter')) $renter->assignRole('renter');

        // 3. Owners
        $owner1 = User::firstOrCreate(
            ['email' => 'owner1@lapak.in'],
            ['name' => 'Owner 1', 'username' => 'owner1', 'phone' => '+6281234567891', 'password' => $defaultPassword, 'ver_status' => $verifiedStatus, 'email_verified_at' => now()]
        );
        $owner1->assignRole(['renter', 'owner']);

        $owner2 = User::firstOrCreate(
            ['email' => 'owner2@lapak.in'],
            ['name' => 'Owner 2', 'username' => 'owner2', 'phone' => '+6281234567892', 'password' => $defaultPassword, 'ver_status' => $verifiedStatus, 'email_verified_at' => now()]
        );
        $owner2->assignRole(['renter', 'owner']); 

        $owner3 = User::firstOrCreate(
            ['email' => 'owner3@lapak.in'],
            ['name' => 'Owner 3 Ruko', 'username' => 'owner3', 'phone' => '+6281234567893', 'password' => $defaultPassword, 'ver_status' => $verifiedStatus, 'email_verified_at' => now()]
        );
        $owner3->assignRole(['renter', 'owner']);

        $this->command->info('Users seeded successfully!');
    }
}