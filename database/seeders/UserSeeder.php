<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('pass123'); 

        //admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@lapak.in'],
            [
                'username' => 'admin',
                'name' => 'System Administrator',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // renter
        $renter = User::firstOrCreate(
            ['email' => 'renter@lapak.in'],
            [
                'username' => 'renter',
                'name' => 'Mas Renter',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );

        if (!$renter->hasRole('renter')) {
            $renter->assignRole('renter');
        }

        $this->command->info('Users seeded successfully!');
        $this->command->line('👉 Admin: admin@lapak.in | PW: password123');
        $this->command->line('👉 Renter: renter@lapak.in | PW: password123');
    }
}