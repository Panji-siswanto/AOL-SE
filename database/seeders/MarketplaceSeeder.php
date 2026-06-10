<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\Location;
use App\Models\PricingType;
use App\Models\Space;
use App\Models\SpaceRegistration;
use App\Models\Status;
use App\Models\User;
use App\Models\Rent;          // Added Rent for Contract Activation
use App\Models\RentRequest;   
use App\Models\RentMessage;  
use App\Models\RentReschedule; 
use Carbon\Carbon;            
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('dummy');

        // Space Statuses
        $regPending = Status::where('code', 'reg_pending')->value('id');
        $regApproved = Status::where('code', 'reg_approved')->value('id');
        $spcAvailable = Status::where('code', 'spc_available')->value('id');
        $verifiedStatus = Status::where('code', 'usr_verified')->value('id');
        
        // Rent Statuses (Bypassed rnt_req_accepted)
        $rntReqPending = Status::where('code', 'rnt_req_pending')->value('id');
        $rntReqRejected = Status::where('code', 'rnt_req_rejected')->value('id');
        $rntReqCancelled = Status::where('code', 'rnt_req_cancelled')->value('id');
        
        $rntOngoing = Status::where('code', 'rnt_ongoing')->value('id');
        $rntCompleted = Status::where('code', 'rnt_completed')->value('id');
        
        // Message Types
        $msgApplication = Status::where('code', 'msg_application')->value('id');
        $msgApproveNote = Status::where('code', 'msg_approval_note')->value('id');
        $msgDeclineReason = Status::where('code', 'msg_decline_reason')->value('id');
        $msgReschedule = Status::where('code', 'msg_reschedule_proposal')->value('id');
        $msgFinishReq = Status::where('code', 'msg_finish_request')->value('id');

        $dailyPricing = PricingType::where('code', 'daily')->value('id');
        $weeklyPricing = PricingType::where('code', 'weekly')->value('id');
        $monthlyPricing = PricingType::where('code', 'monthly')->value('id');

        $suratTanahTypeId = DocumentType::where('code', 'surat_tanah')->value('id');
        $perjanjianSewaTypeId = DocumentType::where('code', 'perjanjian_sewa')->value('id');

        Role::firstOrCreate(['name' => 'renter']);
        Role::firstOrCreate(['name' => 'owner']);

        $password = Hash::make('pass123');
        
        $owner1 = User::firstOrCreate(
            ['email' => 'owner1@lapak.in'],
            ['name' => 'Owner 1', 'username' => 'owner1', 'phone' => '+6281234567891', 'password' => $password, 'ver_status' => $verifiedStatus, 'email_verified_at' => now()]
        );
        $owner1->assignRole(['renter', 'owner']);

        $owner2 = User::firstOrCreate(
            ['email' => 'owner2@lapak.in'],
            ['name' => 'Owner 2', 'username' => 'owner2', 'phone' => '+6281234567892', 'password' => $password, 'ver_status' => $verifiedStatus, 'email_verified_at' => now()]
        );
        $owner2->assignRole('renter','owner'); 

        $owner3 = User::firstOrCreate(
            ['email' => 'owner3@lapak.in'],
            ['name' => 'Owner 3 Ruko', 'username' => 'owner3', 'phone' => '+6281234567893', 'password' => $password, 'ver_status' => $verifiedStatus, 'email_verified_at' => now()]
        );
        $owner3->assignRole(['renter', 'owner']);

        $loc1 = Location::firstOrCreate(['address' => 'Jl. Kemanggisan Ilir III No. 45, Palmerah'], ['city' => 'Jakarta Barat', 'province' => 'DKI Jakarta', 'latitude' => -6.1947, 'longitude' => 106.7865]);
        $reg1 = SpaceRegistration::firstOrCreate(['name' => 'Booth Area Tuku Kemanggisan'], ['owner_id' => $owner1->id, 'location_id' => $loc1->id, 'description' => 'Lapak strategis persis di sebelah kedai Kopi Tuku.', 'length' => 2.0, 'width' => 2.0, 'area' => 4.0, 'status_id' => $regApproved]);
        if ($reg1->wasRecentlyCreated) {
            $reg1->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => 1500000]);
            $reg1->documents()->createMany([['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_1.pdf', 'description' => 'Sertifikat Hak Milik']]);
            $reg1->photos()->create(['file_path' => 'dummy/space_1.jpg', 'is_primary' => true]);
            Space::create(['owner_id' => $owner1->id, 'location_id' => $loc1->id, 'registration_id' => $reg1->id, 'name' => $reg1->name, 'description' => $reg1->description, 'length' => 2.0, 'width' => 2.0, 'area' => 4.0, 'price' => 1500000, 'status_id' => $spcAvailable]);
        }

        $loc2 = Location::firstOrCreate(['address' => 'Jl. Boulevard Alam Sutera, Serpong'], ['city' => 'Tangerang Selatan', 'province' => 'Banten', 'latitude' => -6.2238, 'longitude' => 106.6492]);
        $reg2 = SpaceRegistration::firstOrCreate(['name' => 'Lapak Tenda Malam Alam Sutera'], ['owner_id' => $owner2->id, 'location_id' => $loc2->id, 'description' => 'Hanya tersedia malam hari. Area luas.', 'length' => null, 'width' => null, 'area' => 15.5, 'status_id' => $regPending]);
        if ($reg2->wasRecentlyCreated) {
            $reg2->prices()->create(['pricing_type_id' => $dailyPricing, 'price' => 50000]);
            $reg2->documents()->createMany([['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_2.pdf', 'description' => 'Sertifikat']]);
            $reg2->photos()->create(['file_path' => 'dummy/space_2.jpg', 'is_primary' => true]);
        }

        $loc3 = Location::firstOrCreate(['address' => 'Pasar Modern BSD City'], ['city' => 'Tangerang', 'province' => 'Banten', 'latitude' => -6.3056, 'longitude' => 106.6669]);
        $reg3Live = SpaceRegistration::firstOrCreate(['name' => 'Kios Pasar Modern BSD'], ['owner_id' => $owner3->id, 'location_id' => $loc3->id, 'description' => 'Kios permanen di dalam pasar.', 'length' => 3.0, 'width' => 4.0, 'area' => 12.0, 'status_id' => $regApproved]);
        if ($reg3Live->wasRecentlyCreated) {
            $reg3Live->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => 3000000]);
            $reg3Live->documents()->createMany([['document_type_id' => $suratTanahTypeId, 'file_path' => 'dummy/sertifikat_3a.pdf', 'description' => 'Sertifikat Kios']]);
            $reg3Live->photos()->create(['file_path' => 'dummy/space_1.jpg', 'is_primary' => true]);
            Space::create(['owner_id' => $owner3->id, 'location_id' => $loc3->id, 'registration_id' => $reg3Live->id, 'name' => $reg3Live->name, 'description' => $reg3Live->description, 'length' => 3.0, 'width' => 4.0, 'area' => 12.0, 'price' => 3000000, 'status_id' => $spcAvailable]);
        }

  
        $newLiveSpaces = [
            ['owner' => $owner1, 'name' => 'Lapak Kuliner Binus Syahdan', 'address' => 'Jl. K.H. Syahdan No. 9', 'city' => 'Jakarta Barat', 'province' => 'DKI Jakarta', 'lat' => -6.2001, 'lng' => 106.7854, 'area' => 6.0, 'price' => 1200000, 'pricing' => $monthlyPricing],
            ['owner' => $owner1, 'name' => 'Booth Pameran Mal Taman Anggrek', 'address' => 'Letjen S. Parman St No.28', 'city' => 'Jakarta Barat', 'province' => 'DKI Jakarta', 'lat' => -6.1785, 'lng' => 106.7922, 'area' => 9.0, 'price' => 500000, 'pricing' => $dailyPricing],
            ['owner' => $owner2, 'name' => 'Area Foodtruck Alam Sutera', 'address' => 'Kawasan CBD Alam Sutera', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.2250, 'lng' => 106.6500, 'area' => 18.0, 'price' => 3500000, 'pricing' => $monthlyPricing],
            ['owner' => $owner2, 'name' => 'Kios Tenda Pasar Lama', 'address' => 'Kawasan Kuliner Pasar Lama', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.1702, 'lng' => 106.6333, 'area' => 4.0, 'price' => 75000, 'pricing' => $dailyPricing],
            ['owner' => $owner3, 'name' => 'Ruko Sentra Gading Serpong', 'address' => 'Jl. Boulevard Gading Serpong', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.2400, 'lng' => 106.6288, 'area' => 24.0, 'price' => 6000000, 'pricing' => $monthlyPricing],
            ['owner' => $owner3, 'name' => 'Emperan Ruko Karawaci', 'address' => 'Supermal Karawaci Area', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.2260, 'lng' => 106.6074, 'area' => 2.5, 'price' => 850000, 'pricing' => $monthlyPricing],
            ['owner' => $owner3, 'name' => 'Lapak Stasiun Rawa Buntu', 'address' => 'Area Parkir Stasiun Rawa Buntu', 'city' => 'Tangerang Selatan', 'province' => 'Banten', 'lat' => -6.3194, 'lng' => 106.6836, 'area' => 3.0, 'price' => 100000, 'pricing' => $dailyPricing],
            ['owner' => $owner1, 'name' => 'Bazar Dadakan Senayan', 'address' => 'Area Parkir Timur Senayan', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta', 'lat' => -6.2146, 'lng' => 106.8015, 'area' => 4.0, 'price' => 250000, 'pricing' => $dailyPricing],
            
            ['owner' => $owner2, 'name' => 'Lapak CFD Bundaran HI', 'address' => 'Jl. M.H. Thamrin', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta', 'lat' => -6.1948, 'lng' => 106.8231, 'area' => 2.0, 'price' => 150000, 'pricing' => $dailyPricing, 'extra_pricing' => true],
            
            ['owner' => $owner3, 'name' => 'Kios Blok M Square', 'address' => 'Jl. Melawai 5', 'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta', 'lat' => -6.2444, 'lng' => 106.8006, 'area' => 6.0, 'price' => 2000000, 'pricing' => $monthlyPricing],
            ['owner' => $owner1, 'name' => 'Foodcourt PIK 2', 'address' => 'Pantai Indah Kapuk 2', 'city' => 'Jakarta Utara', 'province' => 'DKI Jakarta', 'lat' => -6.0846, 'lng' => 106.7380, 'area' => 12.0, 'price' => 5000000, 'pricing' => $monthlyPricing],
            
            ['owner' => $owner2, 'name' => 'Lahan Kosong Margonda', 'address' => 'Jl. Margonda Raya', 'city' => 'Depok', 'province' => 'Jawa Barat', 'lat' => -6.3732, 'lng' => 106.8340, 'area' => 20.0, 'price' => 4500000, 'pricing' => $monthlyPricing, 'extra_pricing' => true],
            
            ['owner' => $owner3, 'name' => 'Stand Pameran ICE BSD', 'address' => 'Jl. BSD Grand Boulevard', 'city' => 'Tangerang', 'province' => 'Banten', 'lat' => -6.2986, 'lng' => 106.6358, 'area' => 9.0, 'price' => 1500000, 'pricing' => $dailyPricing],
        ];

        foreach ($newLiveSpaces as $index => $data) {
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
                
                if (isset($data['extra_pricing']) && $data['extra_pricing'] === true) {
                    if ($data['pricing'] === $dailyPricing) {
                        $reg->prices()->create(['pricing_type_id' => $weeklyPricing, 'price' => $data['price'] * 6]);
                        $reg->prices()->create(['pricing_type_id' => $monthlyPricing, 'price' => $data['price'] * 20]);
                    } else {
                        $reg->prices()->create(['pricing_type_id' => $dailyPricing, 'price' => round($data['price'] / 20)]);
                        $reg->prices()->create(['pricing_type_id' => $weeklyPricing, 'price' => round($data['price'] / 4)]);
                    }
                }

                $reg->documents()->create(['document_type_id' => $perjanjianSewaTypeId, 'file_path' => 'dummy/izin_mass.pdf', 'description' => 'Izin Auto-Generated']);
                $photoNum = ($index % 2) + 1;
                $reg->photos()->create(['file_path' => 'dummy/space_' . $photoNum . '.jpg', 'is_primary' => true]);

                Space::create([
                    'owner_id'        => $data['owner']->id,
                    'location_id'     => $loc->id,
                    'registration_id' => $reg->id,
                    'name'            => $reg->name,
                    'description'     => $reg->description,
                    'length'          => null,
                    'width'           => null,
                    'area'            => $data['area'],
                    'price'           => $reg->prices()->min('price'), 
                    'status_id'       => $spcAvailable,
                ]);
            }
        }


        $renter = User::where('email', 'renter@lapak.in')->first();
        
        if ($renter) {
            $space1 = Space::where('name', 'Lahan Kosong Margonda')->first();
            if ($space1) {
                $price1 = 4500000;
                $breakdown1 = ['monthly' => ['qty' => 1, 'unit_price' => $price1, 'subtotal' => $price1]];
                
                $req1 = RentRequest::create([
                    'renter_id'       => $renter->id,
                    'space_id'        => $space1->id,
                    'start_date'      => Carbon::now()->addDays(10)->toDateString(),
                    'end_date'        => Carbon::now()->addDays(40)->toDateString(),
                    'visit_date'      => Carbon::now()->addDays(5)->toDateString(),
                    'total_price'     => $price1,
                    'price_breakdown' => $breakdown1,
                    'status_id'       => $rntReqPending,
                ]);
                
                RentMessage::create(['request_id' => $req1->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Hi Owner 2! I'd love to rent this for a month."]);
                
                $newPrice = $price1 + 225000; 
                $newBreakdown = [
                    'monthly' => ['qty' => 1, 'unit_price' => $price1, 'subtotal' => $price1],
                    'daily' => ['qty' => 1, 'unit_price' => 225000, 'subtotal' => 225000]
                ];
                RentReschedule::create([
                    'rent_request_id'      => $req1->id,
                    'sender_id'            => $owner2->id,
                    'proposed_visit_date'  => Carbon::now()->addDays(6)->toDateString(),
                    'proposed_start_date'  => Carbon::now()->addDays(10)->toDateString(),
                    'proposed_end_date'    => Carbon::now()->addDays(41)->toDateString(),
                    'proposed_total_price' => $newPrice,
                    'price_breakdown'      => $newBreakdown,
                ]);
                RentMessage::create(['request_id' => $req1->id, 'sender_id' => $owner2->id, 'type_id' => $msgReschedule, 'message' => "I have a previous booking ending on the 9th, so I added an extra day for you to set up. Hope this works!"]);
            }

            $space2 = Space::where('name', 'Lapak CFD Bundaran HI')->first();
            if ($space2) {
                $price2 = 150000; 
                $breakdown2 = ['daily' => ['qty' => 2, 'unit_price' => $price2, 'subtotal' => $price2 * 2]];
                
                $req2 = RentRequest::create([
                    'renter_id'       => $renter->id,
                    'space_id'        => $space2->id,
                    'start_date'      => Carbon::now()->addDays(20)->toDateString(),
                    'end_date'        => Carbon::now()->addDays(22)->toDateString(),
                    'visit_date'      => null, 
                    'total_price'     => $price2 * 2,
                    'price_breakdown' => $breakdown2,
                    'status_id'       => $rntReqPending,
                ]);
                RentMessage::create(['request_id' => $req2->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Hello, wanting to rent this for the weekend event."]);
            }

            // Scenario 3: Instantly Ongoing
            $space3 = Space::where('name', 'Area Foodtruck Alam Sutera')->first();
            if ($space3) {
                $price3 = 3500000; 
                $breakdown3 = ['monthly' => ['qty' => 2, 'unit_price' => $price3, 'subtotal' => $price3 * 2]];
                
                $req3 = RentRequest::create([
                    'renter_id'       => $renter->id,
                    'space_id'        => $space3->id,
                    'start_date'      => Carbon::now()->addDays(5)->toDateString(),
                    'end_date'        => Carbon::now()->addDays(65)->toDateString(),
                    'visit_date'      => Carbon::now()->addDays(2)->toDateString(),
                    'total_price'     => $price3 * 2,
                    'price_breakdown' => $breakdown3,
                    'status_id'       => $rntOngoing,
                ]);
                Rent::create([
                    'request_id'      => $req3->id,
                    'space_id'        => $space3->id,
                    'space_name'      => $space3->name,
                    'price'           => $price3 * 2,
                    'pricing_type'    => 'dynamic_combination',
                    'space_length'    => null,
                    'space_width'     => null,
                    'space_area'      => 18.0,
                    'space_address'   => $space3->location->address,
                    'space_latitude'  => $space3->location->latitude,
                    'space_longitude' => $space3->location->longitude,
                    'renter_id'       => $renter->id,
                    'start_date'      => $req3->start_date,
                    'end_date'        => $req3->end_date,
                    'status_id'       => $rntOngoing
                ]);

                RentMessage::create(['request_id' => $req3->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "I have a new food truck concept."]);
                RentMessage::create(['request_id' => $req3->id, 'sender_id' => $owner2->id, 'type_id' => $msgApproveNote, 'message' => "Sounds delicious! See you at the visit date!"]);
            }

            // Scenario 4: Rejected Request
            $space4 = Space::where('name', 'Kios Tenda Pasar Lama')->first();
            if ($space4) {
                $price4 = 75000; 
                $breakdown4 = ['daily' => ['qty' => 5, 'unit_price' => $price4, 'subtotal' => $price4 * 5]];
                
                $req4 = RentRequest::create([
                    'renter_id'       => $renter->id,
                    'space_id'        => $space4->id,
                    'start_date'      => Carbon::now()->addDays(2)->toDateString(),
                    'end_date'        => Carbon::now()->addDays(7)->toDateString(),
                    'visit_date'      => Carbon::now()->addDays(1)->toDateString(),
                    'total_price'     => $price4 * 5,
                    'price_breakdown' => $breakdown4,
                    'status_id'       => $rntReqRejected,
                ]);
                RentMessage::create(['request_id' => $req4->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Need this immediately!"]);
                RentMessage::create(['request_id' => $req4->id, 'sender_id' => $owner2->id, 'type_id' => $msgDeclineReason, 'message' => "Sorry, the space is currently undergoing sudden maintenance. Try again next week!"]);
            }


            $space5 = Space::where('name', 'Lapak Tenda Malam Alam Sutera')->first();
            if ($space5) {
                $price5 = 50000;
                $req5 = RentRequest::create([
                    'renter_id'       => $renter->id,
                    'space_id'        => $space5->id,
                    'start_date'      => Carbon::now()->addDays(15)->toDateString(),
                    'end_date'        => Carbon::now()->addDays(18)->toDateString(),
                    'visit_date'      => Carbon::now()->addDays(10)->toDateString(),
                    'total_price'     => $price5 * 3,
                    'price_breakdown' => ['daily' => ['qty' => 3, 'unit_price' => $price5, 'subtotal' => $price5 * 3]],
                    'status_id'       => $rntReqPending,
                ]);
                RentMessage::create(['request_id' => $req5->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Hi, I want to try opening my noodle stall here for 3 days."]);
            }

            $space6 = Space::where('name', 'Area Foodtruck Alam Sutera')->first();  
            if ($space6) {
                $price6 = 3500000;
                $req6 = RentRequest::create([
                    'renter_id'       => $renter->id,
                    'space_id'        => $space6->id,
                    'start_date'      => Carbon::now()->addDays(30)->toDateString(),
                    'end_date'        => Carbon::now()->addDays(60)->toDateString(),
                    'visit_date'      => Carbon::now()->addDays(20)->toDateString(),
                    'total_price'     => $price6,
                    'price_breakdown' => ['monthly' => ['qty' => 1, 'unit_price' => $price6, 'subtotal' => $price6]],
                    'status_id'       => $rntReqPending,
                ]);
                RentMessage::create(['request_id' => $req6->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Hi, I need this for next month."]);
                
                // Owner counters first
                RentReschedule::create([
                    'rent_request_id'      => $req6->id,
                    'sender_id'            => $owner2->id,
                    'proposed_visit_date'  => Carbon::now()->addDays(22)->toDateString(),
                    'proposed_start_date'  => Carbon::now()->addDays(35)->toDateString(),
                    'proposed_end_date'    => Carbon::now()->addDays(65)->toDateString(),
                    'proposed_total_price' => $price6,
                    'price_breakdown'      => ['monthly' => ['qty' => 1, 'unit_price' => $price6, 'subtotal' => $price6]],
                ]);
                RentMessage::create(['request_id' => $req6->id, 'sender_id' => $owner2->id, 'type_id' => $msgReschedule, 'message' => "I can only do it starting from the 35th day."]);

                // Renter counters back!
                RentReschedule::create([
                    'rent_request_id'      => $req6->id,
                    'sender_id'            => $renter->id,
                    'proposed_visit_date'  => Carbon::now()->addDays(25)->toDateString(),
                    'proposed_start_date'  => Carbon::now()->addDays(35)->toDateString(),
                    'proposed_end_date'    => Carbon::now()->addDays(65)->toDateString(),
                    'proposed_total_price' => $price6,
                    'price_breakdown'      => ['monthly' => ['qty' => 1, 'unit_price' => $price6, 'subtotal' => $price6]],
                ]);
                RentMessage::create(['request_id' => $req6->id, 'sender_id' => $renter->id, 'type_id' => $msgReschedule, 'message' => "That's fine, but I need to push the visit date back a bit."]);
            }

            $space7 = Space::where('name', 'Lapak CFD Bundaran HI')->first(); 
            if ($space7) {
                $price7 = 150000;
                $req7 = RentRequest::create([
                    'renter_id'       => $renter->id,
                    'space_id'        => $space7->id,
                    'start_date'      => Carbon::now()->addDays(14)->toDateString(),
                    'end_date'        => Carbon::now()->addDays(15)->toDateString(),
                    'visit_date'      => null,
                    'total_price'     => $price7 * 1,
                    'price_breakdown' => ['daily' => ['qty' => 1, 'unit_price' => $price7, 'subtotal' => $price7]],
                    'status_id'       => $rntReqPending,
                ]);
                RentMessage::create(['request_id' => $req7->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Can I rent this for next Sunday?"]);
                
                RentReschedule::create([
                    'rent_request_id'      => $req7->id,
                    'sender_id'            => $owner2->id,
                    'proposed_visit_date'  => null,
                    'proposed_start_date'  => Carbon::now()->addDays(21)->toDateString(),
                    'proposed_end_date'    => Carbon::now()->addDays(22)->toDateString(),
                    'proposed_total_price' => $price7,
                    'price_breakdown'      => ['daily' => ['qty' => 1, 'unit_price' => $price7, 'subtotal' => $price7]],
                ]);
                RentMessage::create(['request_id' => $req7->id, 'sender_id' => $owner2->id, 'type_id' => $msgReschedule, 'message' => "Next Sunday is fully booked, how about the Sunday after that?"]);
            }

            $space8 = Space::where('name', 'Lahan Kosong Margonda')->first(); 
            if ($space8) {
                $price8 = 4500000;
                $req8 = RentRequest::create([
                    'renter_id'       => $renter->id,
                    'space_id'        => $space8->id,
                    'start_date'      => Carbon::now()->addDays(60)->toDateString(),
                    'end_date'        => Carbon::now()->addDays(240)->toDateString(),
                    'visit_date'      => Carbon::now()->addDays(10)->toDateString(),
                    'total_price'     => $price8 * 6,
                    'price_breakdown' => ['monthly' => ['qty' => 6, 'unit_price' => $price8, 'subtotal' => $price8 * 6]],
                    'status_id'       => $rntReqPending,
                ]);
                RentMessage::create(['request_id' => $req8->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Planning a massive 6-month activation here. Let me know!"]);
            }

            // Scenario 9: Currently Ongoing (Standard)
            $space9 = Space::where('name', 'Bazar Dadakan Senayan')->first(); 
            if ($space9) {
                $price9 = 250000;
                $req9 = RentRequest::create([
                    'renter_id'       => $renter->id, 'space_id' => $space9->id,
                    'start_date'      => Carbon::now()->subDays(2)->toDateString(), 
                    'end_date'        => Carbon::now()->addDays(5)->toDateString(), 
                    'visit_date'      => Carbon::now()->subDays(5)->toDateString(),
                    'total_price'     => $price9 * 7, 'price_breakdown' => ['daily' => ['qty' => 7, 'unit_price' => $price9, 'subtotal' => $price9 * 7]],
                    'status_id'       => $rntOngoing, 
                ]);
                Rent::create([
                    'request_id' => $req9->id, 'space_id' => $space9->id, 'space_name' => $space9->name, 'price' => $price9 * 7, 'pricing_type' => 'dynamic_combination',
                    'space_length' => null, 'space_width' => null, 'space_area' => 4.0, 'space_address' => $space9->location->address, 'space_latitude' => $space9->location->latitude, 'space_longitude' => $space9->location->longitude,
                    'renter_id' => $renter->id, 'start_date' => $req9->start_date, 'end_date' => $req9->end_date, 'status_id' => $rntOngoing
                ]);
                RentMessage::create(['request_id' => $req9->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Excited for this week!"]);
                RentMessage::create(['request_id' => $req9->id, 'sender_id' => $owner1->id, 'type_id' => $msgApproveNote, 'message' => "Welcome! Keys are at the desk."]);
            }

            // Scenario 10: Early Finish Requested (Renter wants to leave early)
            $space10 = Space::where('name', 'Ruko Sentra Gading Serpong')->first(); 
            if ($space10) {
                $price10 = 6000000;
                $req10 = RentRequest::create([
                    'renter_id'       => $renter->id, 'space_id' => $space10->id,
                    'start_date'      => Carbon::now()->subMonths(1)->toDateString(), 
                    'end_date'        => Carbon::now()->addMonths(2)->toDateString(), 
                    'visit_date'      => Carbon::now()->subMonths(1)->subDays(3)->toDateString(),
                    'total_price'     => $price10 * 3, 'price_breakdown' => ['monthly' => ['qty' => 3, 'unit_price' => $price10, 'subtotal' => $price10 * 3]],
                    'status_id'       => $rntOngoing,
                ]);
                Rent::create([
                    'request_id' => $req10->id, 'space_id' => $space10->id, 'space_name' => $space10->name, 'price' => $price10 * 3, 'pricing_type' => 'dynamic_combination',
                    'space_length' => 3.0, 'space_width' => 4.0, 'space_area' => 24.0, 'space_address' => $space10->location->address, 'space_latitude' => $space10->location->latitude, 'space_longitude' => $space10->location->longitude,
                    'renter_id' => $renter->id, 'start_date' => $req10->start_date, 'end_date' => $req10->end_date, 'status_id' => $rntOngoing
                ]);
                RentMessage::create(['request_id' => $req10->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "3 Month lease please."]);
                RentMessage::create(['request_id' => $req10->id, 'sender_id' => $owner3->id, 'type_id' => $msgApproveNote, 'message' => "Approved."]);
                
                // Renter requests to finish early today!
                RentMessage::create(['request_id' => $req10->id, 'sender_id' => $renter->id, 'type_id' => $msgFinishReq, 'message' => "Hi Owner, my business is relocating early. Can we terminate the contract at the end of this week?"]);
            }

            // Scenario 11: Completely Finished History
            $space11 = Space::where('name', 'Emperan Ruko Karawaci')->first(); 
            if ($space11) {
                $price11 = 850000;
                $req11 = RentRequest::create([
                    'renter_id'       => $renter->id, 'space_id' => $space11->id,
                    'start_date'      => Carbon::now()->subMonths(4)->toDateString(), 
                    'end_date'        => Carbon::now()->subMonths(3)->toDateString(), 
                    'visit_date'      => Carbon::now()->subMonths(4)->subDays(2)->toDateString(),
                    'total_price'     => $price11, 'price_breakdown' => ['monthly' => ['qty' => 1, 'unit_price' => $price11, 'subtotal' => $price11]],
                    'status_id'       => $rntCompleted, 
                ]);
                Rent::create([
                    'request_id' => $req11->id, 'space_id' => $space11->id, 'space_name' => $space11->name, 'price' => $price11, 'pricing_type' => 'dynamic_combination',
                    'space_length' => null, 'space_width' => null, 'space_area' => 2.5, 'space_address' => $space11->location->address, 'space_latitude' => $space11->location->latitude, 'space_longitude' => $space11->location->longitude,
                    'renter_id' => $renter->id, 'start_date' => $req11->start_date, 'end_date' => $req11->end_date, 'status_id' => $rntCompleted
                ]);
                RentMessage::create(['request_id' => $req11->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Short pop-up event."]);
            }
        }
    }
}