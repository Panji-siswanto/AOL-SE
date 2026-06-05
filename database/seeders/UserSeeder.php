<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('pass123'); 
        $verifiedStatus = Status::where('code', 'usr_verified')->value('id');
        $unverifiedStatus = Status::where('code', 'usr_unverified')->value('id');

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@lapak.in'],
            [
                'username'          => 'admin',
                'name'              => 'System Administrator',
                'password'          => $defaultPassword,
                'ver_status'        => $verifiedStatus,
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Renter (Now auto-verified so you can test renting immediately!)
        $renter = User::firstOrCreate(
            ['email' => 'renter@lapak.in'],
            [
                'username'          => 'renter',
                'name'              => 'Mas Renter',
                'password'          => $defaultPassword,
                'ver_status'        => $unverifiedStatus,
                'email_verified_at' => now(),
            ]
        );

        if (!$renter->hasRole('renter')) {
            $renter->assignRole('renter');
        }

        $this->command->info('Users seeded successfully!');
        $this->command->line('Admin: admin@lapak.in | PW: pass123');
        $this->command->line('Renter: renter@lapak.in | PW: pass123');
    }
}