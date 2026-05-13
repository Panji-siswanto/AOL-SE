<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            // 1-3: Registration
            ['context' => 'registration', 'code' => 'reg_pending', 'name' => 'Pending'],
            ['context' => 'registration', 'code' => 'reg_approved', 'name' => 'Approved'],
            ['context' => 'registration', 'code' => 'reg_rejected', 'name' => 'Rejected'],

            // 4-5: Spaces
            ['context' => 'spaces', 'code' => 'spc_available', 'name' => 'Available'],
            ['context' => 'spaces', 'code' => 'spc_unavailable', 'name' => 'Unavailable'],

            // 6-9: Rent Requests
            ['context' => 'rent', 'code' => 'rnt_req_pending', 'name' => 'Pending'],
            ['context' => 'rent', 'code' => 'rnt_req_accepted', 'name' => 'Accepted'],
            ['context' => 'rent', 'code' => 'rnt_req_rejected', 'name' => 'Rejected'],
            ['context' => 'rent', 'code' => 'rnt_req_cancelled', 'name' => 'Cancelled'],

            // 10-12: Rents
            ['context' => 'rent_status', 'code' => 'rnt_ongoing', 'name' => 'Ongoing'],
            ['context' => 'rent_status', 'code' => 'rnt_completed', 'name' => 'Completed'],
            ['context' => 'rent_status', 'code' => 'rnt_cancelled', 'name' => 'Cancelled'],

            // 13-14: Messages
            ['context' => 'rent_message', 'code' => 'msg_proposal', 'name' => 'Proposal'],
            ['context' => 'rent_message', 'code' => 'msg_response', 'name' => 'Response'],

            // 15-18: User & Log Verification Statuses (Fixed Names)
            ['context' => 'user_verification', 'code' => 'usr_unverified', 'name' => 'Unverified'],
            ['context' => 'user_verification', 'code' => 'usr_verify_pending', 'name' => 'Pending Review'],
            ['context' => 'user_verification', 'code' => 'usr_verified', 'name' => 'Verified'],
            ['context' => 'user_verification', 'code' => 'usr_rejected', 'name' => 'Rejected'],
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}