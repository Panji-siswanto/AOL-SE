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

        // 3. User Creation 
        $password = Hash::make('pass123');
        
        $owner1 = User::firstOrCreate(
            ['email' => 'owner1@lapak.in'],
            [
                'name'              => 'Owner 1', 
                'username'          => 'owner1', 
                'phone'             => '+6281234567891', 
                'password'          => $password, 
                'ver_status'        => $verifiedStatus,
                'email_verified_at' => now(), 
            ]
        );
        $owner1->assignRole(['renter', 'owner']);

        $owner2 = User::firstOrCreate(
            ['email' => 'owner2@lapak.in'],
            [
                'name'              => 'Owner 2', 
                'username'          => 'owner2', 
                'phone'             => '+6281234567892', 
                'password'          => $password, 
                'ver_status'        => $verifiedStatus,
                'email_verified_at' => now(), 
            ]
        );
        $owner2->assignRole('renter'); 

        $owner3 = User::firstOrCreate(
            ['email' => 'owner3@lapak.in'],
            [
                'name'              => 'Owner 3 Ruko', 
                'username'          => 'owner3', 
                'phone'             => '+6281234567893', 
                'password'          => $password, 
                'ver_status'        => $verifiedStatus,
                'email_verified_at' => now(), 
            ]
        );
        $owner3->assignRole(['renter', 'owner']);

        // ---------------------------------------------------------
        // SCENARIO 1: Owner 1 (Live Space - "Booth Tuku Kemanggisan")
        // ---------------------------------------------------------
        $loc1 = Location::firstOrCreate(
            ['address' => 'Jl. Kemanggisan Ilir III No. 45, Palmerah'], 
            ['city' => 'Jakarta Barat', 'province' => 'DKI Jakarta', 'latitude' => -6.1947, 'longitude' => 106.7865]
        );

        $reg1 = SpaceRegistration::firstOrCreate(
            ['name' => 'Booth Area Tuku Kemanggisan'], 
            ['owner_id' => $owner1->id, 'location_id' => $loc1->id, 'description' => 'Lapak strategis persis di sebelah kedai Kopi Tuku.', 'length' => 2.0, 'width' => 2.0, 'area' => 4.0, 'status_id' => $regApproved]
        );

        if ($reg1->wasRecentlyCreated) {
            $reg1->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => 1500000]);
            $reg1->documents()->createMany([
                ['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_1.pdf', 'description' => 'Sertifikat Hak Milik'],
            ]);

            Space::create([
                'owner_id' => $owner1->id, 'location_id' => $loc1->id, 'registration_id' => $reg1->id, 'name' => $reg1->name, 'description' => $reg1->description, 'length' => 2.0, 'width' => 2.0, 'area' => 4.0, 'price' => 1500000, 'status_id' => $spcAvailable,
            ]);
        }

        // ---------------------------------------------------------
        // SCENARIO 2: Owner 2 (Pending Registration - "Lapak Tenda")
        // ---------------------------------------------------------
        $loc2 = Location::firstOrCreate(
            ['address' => 'Jl. Boulevard Alam Sutera, Serpong'], 
            ['city' => 'Tangerang Selatan', 'province' => 'Banten', 'latitude' => -6.2238, 'longitude' => 106.6492]
        );

        $reg2 = SpaceRegistration::firstOrCreate(
            ['name' => 'Lapak Tenda Malam Alam Sutera'], 
            ['owner_id' => $owner2->id, 'location_id' => $loc2->id, 'description' => 'Hanya tersedia malam hari. Area luas.', 'length' => null, 'width' => null, 'area' => 15.5, 'status_id' => $regPending]
        );

        if ($reg2->wasRecentlyCreated) {
            $reg2->prices()->create(['pricing_type_id' => $dailyPricing, 'price' => 50000]);
            $reg2->documents()->createMany([['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_2.pdf', 'description' => 'Sertifikat']]);
        }

        // ---------------------------------------------------------
        // SCENARIO 3: Owner 3 (Live Space AND Pending Registration)
        // ---------------------------------------------------------
        $loc3 = Location::firstOrCreate(
            ['address' => 'Pasar Modern BSD City'], 
            ['city' => 'Tangerang', 'province' => 'Banten', 'latitude' => -6.3056, 'longitude' => 106.6669]
        );

        $reg3Live = SpaceRegistration::firstOrCreate(
            ['name' => 'Kios Pasar Modern BSD'], 
            ['owner_id' => $owner3->id, 'location_id' => $loc3->id, 'description' => 'Kios permanen di dalam pasar.', 'length' => 3.0, 'width' => 4.0, 'area' => 12.0, 'status_id' => $regApproved]
        );

        if ($reg3Live->wasRecentlyCreated) {
            $reg3Live->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => 3000000]);
            $reg3Live->documents()->createMany([['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_3a.pdf', 'description' => 'Sertifikat Kios']]);

            Space::create([
                'owner_id' => $owner3->id, 'location_id' => $loc3->id, 'registration_id' => $reg3Live->id, 'name' => $reg3Live->name, 'description' => $reg3Live->description, 'length' => 3.0, 'width' => 4.0, 'area' => 12.0, 'price' => 3000000, 'status_id' => $spcAvailable,
            ]);
        }

        $reg3Pending = SpaceRegistration::firstOrCreate(
            ['name' => 'Lahan Emperan BSD'], 
            ['owner_id' => $owner3->id, 'location_id' => $loc3->id, 'description' => 'Lahan kosong depan kios.', 'length' => 1.5, 'width' => 2.0, 'area' => 3.0, 'status_id' => $regPending]
        );

        if ($reg3Pending->wasRecentlyCreated) {
            $reg3Pending->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => 800000]);
            $reg3Pending->documents()->createMany([['document_type_id' => $perjanjianSewaTypeId, 'file_path' => 'dummy/izin_3b.pdf', 'description' => 'Izin Gelar Lapak']]);
        }

        // ---------------------------------------------------------
        // SCENARIO 4: MASS GENERATION (New Live Spaces for Discovery Feed)
        // ---------------------------------------------------------
        $newLiveSpaces = [
            // Original
            ['owner' => $owner1, 'name' => 'Lapak Kuliner Binus Syahdan', 'address' => 'Jl. K.H. Syahdan No. 9', 'city' => 'Jakarta Barat', 'province' => 'DKI Jakarta', 'lat' => -6.2001, 'lng' => 106.7854, 'area' => 6.0, 'price' => 1200000, 'pricing' => $monthlyPricing],
            ['owner' => $owner1, 'name' => 'Booth Pameran Mal Taman Anggrek', 'address' => 'Letjen S. Parman St No.28', 'city' => 'Jakarta Barat', 'province' => 'DKI Jakarta', 'lat' => -6.1785, 'lng' => 106.7922, 'area' => 9.0, 'price' => 500000, 'pricing' => $dailyPricing],
            ['owner' => $owner2, 'name' => 'Area Foodtruck Alam Sutera', 'address' => 'Kawasan CBD Alam Sutera', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.2250, 'lng' => 106.6500, 'area' => 18.0, 'price' => 3500000, 'pricing' => $monthlyPricing],
            ['owner' => $owner2, 'name' => 'Kios Tenda Pasar Lama', 'address' => 'Kawasan Kuliner Pasar Lama', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.1702, 'lng' => 106.6333, 'area' => 4.0, 'price' => 75000, 'pricing' => $dailyPricing],
            ['owner' => $owner3, 'name' => 'Ruko Sentra Gading Serpong', 'address' => 'Jl. Boulevard Gading Serpong', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.2400, 'lng' => 106.6288, 'area' => 24.0, 'price' => 6000000, 'pricing' => $monthlyPricing],
            ['owner' => $owner3, 'name' => 'Emperan Ruko Karawaci', 'address' => 'Supermal Karawaci Area', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.2260, 'lng' => 106.6074, 'area' => 2.5, 'price' => 850000, 'pricing' => $monthlyPricing],
            ['owner' => $owner3, 'name' => 'Lapak Stasiun Rawa Buntu', 'address' => 'Area Parkir Stasiun Rawa Buntu', 'city' => 'Tangerang Selatan', 'province' => 'Banten', 'lat' => -6.3194, 'lng' => 106.6836, 'area' => 3.0, 'price' => 100000, 'pricing' => $dailyPricing],
            
            // New Additions for Quantity
            ['owner' => $owner1, 'name' => 'Bazar Dadakan Senayan', 'address' => 'Area Parkir Timur Senayan', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta', 'lat' => -6.2146, 'lng' => 106.8015, 'area' => 4.0, 'price' => 250000, 'pricing' => $dailyPricing],
            ['owner' => $owner2, 'name' => 'Lapak CFD Bundaran HI', 'address' => 'Jl. M.H. Thamrin', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta', 'lat' => -6.1948, 'lng' => 106.8231, 'area' => 2.0, 'price' => 150000, 'pricing' => $dailyPricing],
            ['owner' => $owner3, 'name' => 'Kios Blok M Square', 'address' => 'Jl. Melawai 5', 'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta', 'lat' => -6.2444, 'lng' => 106.8006, 'area' => 6.0, 'price' => 2000000, 'pricing' => $monthlyPricing],
            ['owner' => $owner1, 'name' => 'Foodcourt PIK 2', 'address' => 'Pantai Indah Kapuk 2', 'city' => 'Jakarta Utara', 'province' => 'DKI Jakarta', 'lat' => -6.0846, 'lng' => 106.7380, 'area' => 12.0, 'price' => 5000000, 'pricing' => $monthlyPricing],
            ['owner' => $owner2, 'name' => 'Lahan Kosong Margonda', 'address' => 'Jl. Margonda Raya', 'city' => 'Depok', 'province' => 'Jawa Barat', 'lat' => -6.3732, 'lng' => 106.8340, 'area' => 20.0, 'price' => 4500000, 'pricing' => $monthlyPricing],
            ['owner' => $owner3, 'name' => 'Stand Pameran ICE BSD', 'address' => 'Jl. BSD Grand Boulevard', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.2986, 'lng' => 106.6358, 'area' => 9.0, 'price' => 1500000, 'pricing' => $dailyPricing],
        ];

        foreach ($newLiveSpaces as $data) {
            $loc = Location::firstOrCreate(
                ['address' => $data['address']], 
                ['city' => $data['city'], 'province' => $data['province'], 'latitude' => $data['lat'], 'longitude' => $data['lng']]
            );

            $reg = SpaceRegistration::firstOrCreate(
                ['name' => $data['name']], 
                ['owner_id' => $data['owner']->id, 'location_id' => $loc->id, 'description' => 'Lapak strategis tersedia untuk disewakan segera.', 'length' => null, 'width' => null, 'area' => $data['area'], 'status_id' => $regApproved]
            );

            if ($reg->wasRecentlyCreated) {
                $reg->prices()->create(['pricing_type_id' => $data['pricing'], 'price' => $data['price']]);
                $reg->documents()->create(['document_type_id' => $perjanjianSewaTypeId, 'file_path' => 'dummy/izin_mass.pdf', 'description' => 'Izin Auto-Generated']);

                Space::create([
                    'owner_id'        => $data['owner']->id,
                    'location_id'     => $loc->id,
                    'registration_id' => $reg->id,
                    'name'            => $reg->name,
                    'description'     => $reg->description,
                    'length'          => null,
                    'width'           => null,
                    'area'            => $data['area'],
                    'price'           => $data['price'],
                    'status_id'       => $spcAvailable,
                ]);
            }
        }
    }
}