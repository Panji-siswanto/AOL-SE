<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            // 1-3: Registration Applications
            ['context' => 'registration', 'code' => 'reg_pending', 'name' => 'Pending Review'],
            ['context' => 'registration', 'code' => 'reg_approved', 'name' => 'Approved'],
            ['context' => 'registration', 'code' => 'reg_rejected', 'name' => 'Rejected'],

            // 4-7: Live Spaces Lifecycle
            ['context' => 'spaces', 'code' => 'spc_available', 'name' => 'Live & Available'],
            ['context' => 'spaces', 'code' => 'spc_paused', 'name' => 'Paused (Hidden)'],
            ['context' => 'spaces', 'code' => 'spc_unlisted', 'name' => 'Unlisted (Archived)'],
            ['context' => 'spaces', 'code' => 'spc_suspended', 'name' => 'Suspended (Admin Action)'],

            // 8-11: Rent Requests (The Initial Application)
            ['context' => 'rent_req', 'code' => 'rnt_req_pending', 'name' => 'Pending'],
            ['context' => 'rent_req', 'code' => 'rnt_req_accepted', 'name' => 'Accepted'],
            ['context' => 'rent_req', 'code' => 'rnt_req_rejected', 'name' => 'Rejected'],
            ['context' => 'rent_req', 'code' => 'rnt_req_cancelled', 'name' => 'Cancelled'],

            // 12-14: Active Rents (The Ongoing Contract)
            ['context' => 'rent_status', 'code' => 'rnt_ongoing', 'name' => 'Ongoing'],
            ['context' => 'rent_status', 'code' => 'rnt_completed', 'name' => 'Completed'],
            ['context' => 'rent_status', 'code' => 'rnt_cancelled', 'name' => 'Cancelled'],

            // 15-25: Messages / Negotiations
            ['context' => 'rent_message', 'code' => 'msg_proposal', 'name' => 'Proposal'],
            ['context' => 'rent_message', 'code' => 'msg_response', 'name' => 'Response'],
            ['context' => 'rent_message', 'code' => 'msg_application', 'name' => 'Application'],
            ['context' => 'rent_message', 'code' => 'msg_decline_reason', 'name' => 'Decline Reason'],
            ['context' => 'rent_message', 'code' => 'msg_reschedule_proposal', 'name' => 'Reschedule Proposal'],
            ['context' => 'rent_message', 'code' => 'msg_reschedule_accepted', 'name' => 'Reschedule Accepted'],
            ['context' => 'rent_message', 'code' => 'msg_reschedule_rejected', 'name' => 'Reschedule Rejected'],

            // 26-29: User Identity Verification
            ['context' => 'user_verification', 'code' => 'usr_unverified', 'name' => 'Unverified'],
            ['context' => 'user_verification', 'code' => 'usr_verify_pending', 'name' => 'Pending Review'],
            ['context' => 'user_verification', 'code' => 'usr_verified', 'name' => 'Verified'],
            ['context' => 'user_verification', 'code' => 'usr_rejected', 'name' => 'Rejected'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}