<?php

namespace Database\Seeders;

use App\Models\Space;
use App\Models\Status;
use App\Models\User;
use App\Models\Rent;
use App\Models\RentRequest;   
use App\Models\RentMessage;  
use App\Models\RentReschedule; 
use Carbon\Carbon;            
use Illuminate\Database\Seeder;

class RentRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Rent Statuses
        $rntReqPending = Status::where('code', 'rnt_req_pending')->value('id');
        $rntReqRejected = Status::where('code', 'rnt_req_rejected')->value('id');
        $rntOngoing = Status::where('code', 'rnt_ongoing')->value('id');
        $rntCompleted = Status::where('code', 'rnt_completed')->value('id');
        
        // Message Types
        $msgApplication = Status::where('code', 'msg_application')->value('id');
        $msgApproveNote = Status::where('code', 'msg_approval_note')->value('id');
        $msgDeclineReason = Status::where('code', 'msg_decline_reason')->value('id');
        $msgReschedule = Status::where('code', 'msg_reschedule_proposal')->value('id');
        $msgFinishReq = Status::where('code', 'msg_finish_request')->value('id');

        $renter = User::where('email', 'renter@lapak.in')->first();
        $owner1 = User::where('email', 'owner1@lapak.in')->first();
        $owner2 = User::where('email', 'owner2@lapak.in')->first();
        $owner3 = User::where('email', 'owner3@lapak.in')->first();
        
        if (!$renter || !$owner1 || !$owner2 || !$owner3) {
            $this->command->error('Users missing. Run UserSeeder first.');
            return;
        }

        // Scenario 1: Pending with Owner Counter-Proposal
        $space1 = Space::where('name', 'Lahan Kosong Margonda')->first();
        if ($space1) {
            $price1 = 4500000;
            $req1 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space1->id,
                'start_date' => Carbon::now()->addDays(10)->toDateString(), 'end_date' => Carbon::now()->addDays(40)->toDateString(),
                'visit_date' => Carbon::now()->addDays(5)->toDateString(), 'total_price' => $price1,
                'price_breakdown' => ['monthly' => ['qty' => 1, 'unit_price' => $price1, 'subtotal' => $price1]], 'status_id' => $rntReqPending,
            ]);
            RentMessage::create(['request_id' => $req1->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Hi Owner 2! I'd love to rent this for a month."]);
            
            RentReschedule::create([
                'rent_request_id' => $req1->id, 'sender_id' => $owner2->id,
                'proposed_visit_date' => Carbon::now()->addDays(6)->toDateString(), 'proposed_start_date' => Carbon::now()->addDays(10)->toDateString(), 'proposed_end_date' => Carbon::now()->addDays(41)->toDateString(),
                'proposed_total_price' => $price1 + 225000, 'price_breakdown' => ['monthly' => ['qty' => 1, 'unit_price' => $price1, 'subtotal' => $price1], 'daily' => ['qty' => 1, 'unit_price' => 225000, 'subtotal' => 225000]],
            ]);
            RentMessage::create(['request_id' => $req1->id, 'sender_id' => $owner2->id, 'type_id' => $msgReschedule, 'message' => "I have a previous booking ending on the 9th, so I added an extra day for you to set up."]);
        }

        // Scenario 2: Simple Pending
        $space2 = Space::where('name', 'Lapak CFD Bundaran HI')->first();
        if ($space2) {
            $price2 = 150000; 
            $req2 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space2->id,
                'start_date' => Carbon::now()->addDays(20)->toDateString(), 'end_date' => Carbon::now()->addDays(22)->toDateString(),
                'visit_date' => null, 'total_price' => $price2 * 2,
                'price_breakdown' => ['daily' => ['qty' => 2, 'unit_price' => $price2, 'subtotal' => $price2 * 2]], 'status_id' => $rntReqPending,
            ]);
            RentMessage::create(['request_id' => $req2->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Hello, wanting to rent this for the weekend event."]);
        }

        // Scenario 3: Ongoing (Active immediately)
        $space3 = Space::where('name', 'Area Foodtruck Alam Sutera')->first();
        if ($space3) {
            $price3 = 3500000; 
            $req3 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space3->id,
                'start_date' => Carbon::now()->addDays(5)->toDateString(), 'end_date' => Carbon::now()->addDays(65)->toDateString(),
                'visit_date' => Carbon::now()->addDays(2)->toDateString(), 'total_price' => $price3 * 2,
                'price_breakdown' => ['monthly' => ['qty' => 2, 'unit_price' => $price3, 'subtotal' => $price3 * 2]], 'status_id' => $rntOngoing,
            ]);
            Rent::create([
                'request_id' => $req3->id, 'space_id' => $space3->id, 'space_name' => $space3->name, 'price' => $price3 * 2, 'pricing_type' => 'dynamic_combination',
                'space_length' => null, 'space_width' => null, 'space_area' => 18.0, 'space_address' => $space3->location->address, 'space_latitude' => $space3->location->latitude, 'space_longitude' => $space3->location->longitude,
                'renter_id' => $renter->id, 'start_date' => $req3->start_date, 'end_date' => $req3->end_date, 'status_id' => $rntOngoing
            ]);
            RentMessage::create(['request_id' => $req3->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "I have a new food truck concept."]);
            RentMessage::create(['request_id' => $req3->id, 'sender_id' => $owner2->id, 'type_id' => $msgApproveNote, 'message' => "Sounds delicious! See you at the visit date!"]);
        }

        // Scenario 4: Rejected Request
        $space4 = Space::where('name', 'Kios Tenda Pasar Lama')->first();
        if ($space4) {
            $price4 = 75000; 
            $req4 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space4->id,
                'start_date' => Carbon::now()->addDays(2)->toDateString(), 'end_date' => Carbon::now()->addDays(7)->toDateString(),
                'visit_date' => Carbon::now()->addDays(1)->toDateString(), 'total_price' => $price4 * 5,
                'price_breakdown' => ['daily' => ['qty' => 5, 'unit_price' => $price4, 'subtotal' => $price4 * 5]], 'status_id' => $rntReqRejected,
            ]);
            RentMessage::create(['request_id' => $req4->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Need this immediately!"]);
            RentMessage::create(['request_id' => $req4->id, 'sender_id' => $owner2->id, 'type_id' => $msgDeclineReason, 'message' => "Sorry, the space is currently undergoing sudden maintenance. Try again next week!"]);
        }

        // Scenario 5: Simple Pending (Fresh Application)
        $space5 = Space::where('name', 'Lapak Tenda Malam Alam Sutera')->first();
        if ($space5) {
            $price5 = 50000;
            $req5 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space5->id,
                'start_date' => Carbon::now()->addDays(15)->toDateString(), 'end_date' => Carbon::now()->addDays(18)->toDateString(),
                'visit_date' => Carbon::now()->addDays(10)->toDateString(), 'total_price' => $price5 * 3,
                'price_breakdown' => ['daily' => ['qty' => 3, 'unit_price' => $price5, 'subtotal' => $price5 * 3]], 'status_id' => $rntReqPending,
            ]);
            RentMessage::create(['request_id' => $req5->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Hi, I want to try opening my noodle stall here for 3 days."]);
        }

        // Scenario 6: Deep Negotiation Cycle (Renter Counter-Proposed)
        $space6 = Space::where('name', 'Area Foodtruck Alam Sutera')->first();  
        if ($space6) {
            $price6 = 3500000;
            $req6 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space6->id,
                'start_date' => Carbon::now()->addDays(30)->toDateString(), 'end_date' => Carbon::now()->addDays(60)->toDateString(),
                'visit_date' => Carbon::now()->addDays(20)->toDateString(), 'total_price' => $price6,
                'price_breakdown' => ['monthly' => ['qty' => 1, 'unit_price' => $price6, 'subtotal' => $price6]], 'status_id' => $rntReqPending,
            ]);
            RentMessage::create(['request_id' => $req6->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Hi, I need this for next month."]);
            
            RentReschedule::create([
                'rent_request_id' => $req6->id, 'sender_id' => $owner2->id,
                'proposed_visit_date' => Carbon::now()->addDays(22)->toDateString(), 'proposed_start_date' => Carbon::now()->addDays(35)->toDateString(), 'proposed_end_date' => Carbon::now()->addDays(65)->toDateString(),
                'proposed_total_price' => $price6, 'price_breakdown' => ['monthly' => ['qty' => 1, 'unit_price' => $price6, 'subtotal' => $price6]],
            ]);
            RentMessage::create(['request_id' => $req6->id, 'sender_id' => $owner2->id, 'type_id' => $msgReschedule, 'message' => "I can only do it starting from the 35th day."]);

            RentReschedule::create([
                'rent_request_id' => $req6->id, 'sender_id' => $renter->id,
                'proposed_visit_date' => Carbon::now()->addDays(25)->toDateString(), 'proposed_start_date' => Carbon::now()->addDays(35)->toDateString(), 'proposed_end_date' => Carbon::now()->addDays(65)->toDateString(),
                'proposed_total_price' => $price6, 'price_breakdown' => ['monthly' => ['qty' => 1, 'unit_price' => $price6, 'subtotal' => $price6]],
            ]);
            RentMessage::create(['request_id' => $req6->id, 'sender_id' => $renter->id, 'type_id' => $msgReschedule, 'message' => "That's fine, but I need to push the visit date back a bit."]);
        }

        // Scenario 7: Owner Countered
        $space7 = Space::where('name', 'Lapak CFD Bundaran HI')->first(); 
        if ($space7) {
            $price7 = 150000;
            $req7 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space7->id,
                'start_date' => Carbon::now()->addDays(14)->toDateString(), 'end_date' => Carbon::now()->addDays(15)->toDateString(),
                'visit_date' => null, 'total_price' => $price7 * 1,
                'price_breakdown' => ['daily' => ['qty' => 1, 'unit_price' => $price7, 'subtotal' => $price7]], 'status_id' => $rntReqPending,
            ]);
            RentMessage::create(['request_id' => $req7->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Can I rent this for next Sunday?"]);
            
            RentReschedule::create([
                'rent_request_id' => $req7->id, 'sender_id' => $owner2->id,
                'proposed_visit_date' => null, 'proposed_start_date' => Carbon::now()->addDays(21)->toDateString(), 'proposed_end_date' => Carbon::now()->addDays(22)->toDateString(),
                'proposed_total_price' => $price7, 'price_breakdown' => ['daily' => ['qty' => 1, 'unit_price' => $price7, 'subtotal' => $price7]],
            ]);
            RentMessage::create(['request_id' => $req7->id, 'sender_id' => $owner2->id, 'type_id' => $msgReschedule, 'message' => "Next Sunday is fully booked, how about the Sunday after that?"]);
        }

        // Scenario 8: Long Term Future Plan 
        $space8 = Space::where('name', 'Lahan Kosong Margonda')->first(); 
        if ($space8) {
            $price8 = 4500000;
            $req8 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space8->id,
                'start_date' => Carbon::now()->addDays(60)->toDateString(), 'end_date' => Carbon::now()->addDays(240)->toDateString(),
                'visit_date' => Carbon::now()->addDays(10)->toDateString(), 'total_price' => $price8 * 6,
                'price_breakdown' => ['monthly' => ['qty' => 6, 'unit_price' => $price8, 'subtotal' => $price8 * 6]], 'status_id' => $rntReqPending,
            ]);
            RentMessage::create(['request_id' => $req8->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Planning a massive 6-month activation here. Let me know!"]);
        }

        // Scenario 9: Currently Ongoing (Standard)
        $space9 = Space::where('name', 'Bazar Dadakan Senayan')->first(); 
        if ($space9) {
            $price9 = 250000;
            $req9 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space9->id,
                'start_date' => Carbon::now()->subDays(2)->toDateString(), 'end_date' => Carbon::now()->addDays(5)->toDateString(), 
                'visit_date' => Carbon::now()->subDays(5)->toDateString(), 'total_price' => $price9 * 7, 
                'price_breakdown' => ['daily' => ['qty' => 7, 'unit_price' => $price9, 'subtotal' => $price9 * 7]], 'status_id' => $rntOngoing, 
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
                'renter_id' => $renter->id, 'space_id' => $space10->id,
                'start_date' => Carbon::now()->subMonths(1)->toDateString(), 'end_date' => Carbon::now()->addMonths(2)->toDateString(), 
                'visit_date' => Carbon::now()->subMonths(1)->subDays(3)->toDateString(), 'total_price' => $price10 * 3, 
                'price_breakdown' => ['monthly' => ['qty' => 3, 'unit_price' => $price10, 'subtotal' => $price10 * 3]], 'status_id' => $rntOngoing,
            ]);
            Rent::create([
                'request_id' => $req10->id, 'space_id' => $space10->id, 'space_name' => $space10->name, 'price' => $price10 * 3, 'pricing_type' => 'dynamic_combination',
                'space_length' => 3.0, 'space_width' => 4.0, 'space_area' => 24.0, 'space_address' => $space10->location->address, 'space_latitude' => $space10->location->latitude, 'space_longitude' => $space10->location->longitude,
                'renter_id' => $renter->id, 'start_date' => $req10->start_date, 'end_date' => $req10->end_date, 'status_id' => $rntOngoing
            ]);
            RentMessage::create(['request_id' => $req10->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "3 Month lease please."]);
            RentMessage::create(['request_id' => $req10->id, 'sender_id' => $owner3->id, 'type_id' => $msgApproveNote, 'message' => "Approved."]);
            
            RentMessage::create(['request_id' => $req10->id, 'sender_id' => $renter->id, 'type_id' => $msgFinishReq, 'message' => "Hi Owner, my business is relocating early. Can we terminate the contract at the end of this week?"]);
        }

        // Scenario 11: Completely Finished History
        $space11 = Space::where('name', 'Emperan Ruko Karawaci')->first(); 
        if ($space11) {
            $price11 = 850000;
            $req11 = RentRequest::create([
                'renter_id' => $renter->id, 'space_id' => $space11->id,
                'start_date' => Carbon::now()->subMonths(4)->toDateString(), 'end_date' => Carbon::now()->subMonths(3)->toDateString(), 
                'visit_date' => Carbon::now()->subMonths(4)->subDays(2)->toDateString(), 'total_price' => $price11, 
                'price_breakdown' => ['monthly' => ['qty' => 1, 'unit_price' => $price11, 'subtotal' => $price11]], 'status_id' => $rntCompleted, 
            ]);
            Rent::create([
                'request_id' => $req11->id, 'space_id' => $space11->id, 'space_name' => $space11->name, 'price' => $price11, 'pricing_type' => 'dynamic_combination',
                'space_length' => null, 'space_width' => null, 'space_area' => 2.5, 'space_address' => $space11->location->address, 'space_latitude' => $space11->location->latitude, 'space_longitude' => $space11->location->longitude,
                'renter_id' => $renter->id, 'start_date' => $req11->start_date, 'end_date' => $req11->end_date, 'status_id' => $rntCompleted
            ]);
            RentMessage::create(['request_id' => $req11->id, 'sender_id' => $renter->id, 'type_id' => $msgApplication, 'message' => "Short pop-up event."]);
        }

        $this->command->info('Rent Requests and Scenarios seeded successfully!');
    }
}