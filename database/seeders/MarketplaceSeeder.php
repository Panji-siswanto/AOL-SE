<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\Location;
use App\Models\PricingType;
use App\Models\Space;
use App\Models\SpaceRegistration;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Status & Type Lookups
        $regPending = Status::where('code', 'reg_pending')->value('id');
        $regApproved = Status::where('code', 'reg_approved')->value('id');
        $spcAvailable = Status::where('code', 'spc_available')->value('id');
        $verifiedStatus = Status::where('code', 'usr_verified')->value('id');

        $dailyPricing = PricingType::where('code', 'daily')->value('id');
        $monthlyPricing = PricingType::where('code', 'monthly')->value('id');

        $suratTanahTypeId = DocumentType::where('code', 'surat_tanah')->value('id');
        $perjanjianSewaTypeId = DocumentType::where('code', 'perjanjian_sewa')->value('id');

        // 2. Ensure Roles Exist
        Role::firstOrCreate(['name' => 'renter']);
        Role::firstOrCreate(['name' => 'owner']);

        // 3. User Creation (Using requested credentials & verified emails)
        $password = Hash::make('pass123');
        
        $owner1 = User::firstOrCreate(
            ['email' => 'owner1@lapak.in'],
            [
                'name' => 'Owner 1', 
                'username' => 'owner1', 
                'phone' => '+6281234567891', 
                'password' => $password, 
                'ver_status' => $verifiedStatus,
                'email_verified_at' => now(), 
            ]
        );
        $owner1->assignRole(['renter', 'owner']);

        $owner2 = User::firstOrCreate(
            ['email' => 'owner2@lapak.in'],
            [
                'name' => 'Owner 2', 
                'username' => 'owner2', 
                'phone' => '+6281234567892', 
                'password' => $password, 
                'ver_status' => $verifiedStatus,
                'email_verified_at' => now(), 
            ]
        );
        $owner2->assignRole('renter'); 

        $owner3 = User::firstOrCreate(
            ['email' => 'owner3@lapak.in'],
            [
                'name' => 'Owner 3 Ruko', 
                'username' => 'owner3', 
                'phone' => '+6281234567893', 
                'password' => $password, 
                'ver_status' => $verifiedStatus,
                'email_verified_at' => now(), 
            ]
        );
        $owner3->assignRole(['renter', 'owner']);

        // ---------------------------------------------------------
        // SCENARIO 1: Owner 1 (Live Space - "Booth Tuku Kemanggisan")
        // ---------------------------------------------------------
        $loc1 = Location::firstOrCreate([
            'address' => 'Jl. Kemanggisan Ilir III No. 45, Palmerah',
        ], [
            'city' => 'Jakarta Barat',
            'province' => 'DKI Jakarta',
            'latitude' => -6.1947,
            'longitude' => 106.7865,
        ]);

        $reg1 = SpaceRegistration::firstOrCreate([
            'name' => 'Booth Area Tuku Kemanggisan',
        ], [
            'owner_id' => $owner1->id,
            'location_id' => $loc1->id,
            'description' => 'Lapak strategis persis di sebelah kedai Kopi Tuku. Traffic mahasiswa Binus sangat ramai setiap hari.',
            'length' => 2.0,
            'width' => 2.0,
            'area' => 4.0, 
            'status_id' => $regApproved,
        ]);

        if ($reg1->wasRecentlyCreated) {
            $reg1->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => 1500000]);
            
            // Seed Legal Documents
            $reg1->documents()->createMany([
                ['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_1.pdf', 'description' => 'Sertifikat Hak Milik'],
                ['document_type_id' => $perjanjianSewaTypeId, 'file_path' => 'dummy/izin_1.pdf', 'description' => 'Surat Izin RT/RW'],
            ]);

            // Seed Cover Photo
            $reg1->photos()->create([
                'file_path' => 'dummy/space_1.jpg',
                'description' => 'Tampak Depan Lapak',
                'is_primary' => true,
            ]);

            Space::create([
                'owner_id' => $owner1->id,
                'location_id' => $loc1->id,
                'registration_id' => $reg1->id,
                'name' => $reg1->name,
                'description' => $reg1->description,
                'length' => 2.0,
                'width' => 2.0,
                'area' => 4.0,
                'price' => 1500000,
                'status_id' => $spcAvailable,
            ]);
        }

        // ---------------------------------------------------------
        // SCENARIO 2: Owner 2 (Pending Registration - "Lapak Tenda")
        // ---------------------------------------------------------
        $loc2 = Location::firstOrCreate([
            'address' => 'Jl. Boulevard Alam Sutera, Serpong',
        ], [
            'city' => 'Tangerang Selatan',
            'province' => 'Banten',
            'latitude' => -6.2238,
            'longitude' => 106.6492,
        ]);

        $reg2 = SpaceRegistration::firstOrCreate([
            'name' => 'Lapak Tenda Malam Alam Sutera',
        ], [
            'owner_id' => $owner2->id,
            'location_id' => $loc2->id,
            'description' => 'Hanya tersedia malam hari. Area luas bisa untuk gelar tikar atau tenda pecel lele. Parkiran luas.',
            'length' => null, 
            'width' => null,
            'area' => 15.5,
            'status_id' => $regPending,
        ]);

        if ($reg2->wasRecentlyCreated) {
            $reg2->prices()->create(['pricing_type_id' => $dailyPricing, 'price' => 50000]);

            $reg2->documents()->createMany([
                ['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_2.pdf', 'description' => 'Sertifikat Tanah Kosong'],
            ]);

            $reg2->photos()->create([
                'file_path' => 'dummy/space_2.jpg',
                'description' => 'Kondisi Lahan Malam Hari',
                'is_primary' => true,
            ]);
        }

        // ---------------------------------------------------------
        // SCENARIO 3: Owner 3 (Live Space AND Pending Registration)
        // ---------------------------------------------------------
        $loc3 = Location::firstOrCreate([
            'address' => 'Pasar Modern BSD City',
        ], [
            'city' => 'Tangerang',
            'province' => 'Banten',
            'latitude' => -6.3056,
            'longitude' => 106.6669,
        ]);

        $reg3Live = SpaceRegistration::firstOrCreate([
            'name' => 'Kios Pasar Modern BSD',
        ], [
            'owner_id' => $owner3->id,
            'location_id' => $loc3->id,
            'description' => 'Kios permanen di dalam pasar modern BSD. Rolling door aman, listrik 900W.',
            'length' => 3.0,
            'width' => 4.0,
            'area' => 12.0,
            'status_id' => $regApproved,
        ]);

        if ($reg3Live->wasRecentlyCreated) {
            $reg3Live->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => 3000000]);
            
            $reg3Live->documents()->createMany([
                ['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_3a.pdf', 'description' => 'Sertifikat Kios'],
            ]);

            $reg3Live->photos()->create([
                'file_path' => 'dummy/space_3a.jpg',
                'description' => 'Tampak Kios Terbuka',
                'is_primary' => true,
            ]);

            Space::create([
                'owner_id' => $owner3->id,
                'location_id' => $loc3->id,
                'registration_id' => $reg3Live->id,
                'name' => $reg3Live->name,
                'description' => $reg3Live->description,
                'length' => 3.0,
                'width' => 4.0,
                'area' => 12.0,
                'price' => 3000000,
                'status_id' => $spcAvailable,
            ]);
        }

        $reg3Pending = SpaceRegistration::firstOrCreate([
            'name' => 'Lahan Emperan BSD',
        ], [
            'owner_id' => $owner3->id,
            'location_id' => $loc3->id,
            'description' => 'Lahan kosong di depan kios saya. Cocok untuk jualan pakai gerobak kecil.',
            'length' => 1.5,
            'width' => 2.0,
            'area' => 3.0,
            'status_id' => $regPending,
        ]);

        if ($reg3Pending->wasRecentlyCreated) {
            $reg3Pending->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => 800000]);

            $reg3Pending->documents()->createMany([
                ['document_type_id' => $perjanjianSewaTypeId, 'file_path' => 'dummy/izin_3b.pdf', 'description' => 'Izin Gelar Lapak dari Pengelola'],
            ]);

            $reg3Pending->photos()->create([
                'file_path' => 'dummy/space_3b.jpg',
                'description' => 'Area Emperan',
                'is_primary' => true,
            ]);
        }
    }
}