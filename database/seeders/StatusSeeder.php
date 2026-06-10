<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['context' => 'user_verification', 'code' => 'usr_unverified', 'name' => 'Unverified'],
            ['context' => 'user_verification', 'code' => 'usr_verify_pending', 'name' => 'Pending Review'],
            ['context' => 'user_verification', 'code' => 'usr_verified', 'name' => 'Verified'],
            ['context' => 'user_verification', 'code' => 'usr_rejected', 'name' => 'Rejected'],

            ['context' => 'registration', 'code' => 'reg_pending', 'name' => 'Pending Review'],
            ['context' => 'registration', 'code' => 'reg_approved', 'name' => 'Approved'],
            ['context' => 'registration', 'code' => 'reg_rejected', 'name' => 'Rejected'],

            ['context' => 'spaces', 'code' => 'spc_available', 'name' => 'Live & Available'],
            ['context' => 'spaces', 'code' => 'spc_paused', 'name' => 'Paused (Hidden)'],
            ['context' => 'spaces', 'code' => 'spc_unlisted', 'name' => 'Unlisted (Archived)'],
            ['context' => 'spaces', 'code' => 'spc_suspended', 'name' => 'Suspended (Admin Action)'],

            ['context' => 'rent_req', 'code' => 'rnt_req_pending', 'name' => 'Pending'],
            ['context' => 'rent_req', 'code' => 'rnt_req_accepted', 'name' => 'Accepted'],
            ['context' => 'rent_req', 'code' => 'rnt_req_rejected', 'name' => 'Rejected'],
            ['context' => 'rent_req', 'code' => 'rnt_req_cancelled', 'name' => 'Cancelled'],

            ['context' => 'rent_status', 'code' => 'rnt_ongoing', 'name' => 'Ongoing'],
            ['context' => 'rent_status', 'code' => 'rnt_completed', 'name' => 'Completed'],
            ['context' => 'rent_status', 'code' => 'rnt_cancelled', 'name' => 'Cancelled'],
            
            ['context' => 'rent_message', 'code' => 'msg_application', 'name' => 'Proposal Pitch'],
            ['context' => 'rent_message', 'code' => 'msg_approval_note', 'name' => 'Approval Note'],
            ['context' => 'rent_message', 'code' => 'msg_decline_reason', 'name' => 'Decline Reason'],
            ['context' => 'rent_message', 'code' => 'msg_reschedule_proposal', 'name' => 'Reschedule Proposal'],
            ['context' => 'rent_message', 'code' => 'msg_reschedule_accepted', 'name' => 'Reschedule Accepted'],
            ['context' => 'rent_message', 'code' => 'msg_reschedule_rejected', 'name' => 'Reschedule Rejected'],
            
            ['context' => 'rent_message', 'code' => 'msg_finish_request', 'name' => 'Early Finish Request'],
            ['context' => 'rent_message', 'code' => 'msg_finish_accepted', 'name' => 'Early Finish Accepted'],
            ['context' => 'rent_message', 'code' => 'msg_finish_rejected', 'name' => 'Early Finish Rejected'],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}