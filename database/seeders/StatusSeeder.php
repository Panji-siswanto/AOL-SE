<?php

namespace Database\Seeders;
use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        $statuses = [

            // registration
            ['context' => 'registration', 'code' => 'reg_pending', 'name' => 'Pending'],
            ['context' => 'registration', 'code' => 'reg_approved', 'name' => 'Approved'],
            ['context' => 'registration', 'code' => 'reg_rejected', 'name' => 'Rejected'],

            // spaces
            ['context' => 'spaces', 'code' => 'spc_available', 'name' => 'Available'],
            ['context' => 'spaces', 'code' => 'spc_unavailable', 'name' => 'Unavailable'],

            // rent requests
            ['context' => 'rent', 'code' => 'rnt_req_pending', 'name' => 'Pending'],
            ['context' => 'rent', 'code' => 'rnt_req_accepted', 'name' => 'Accepted'],
            ['context' => 'rent', 'code' => 'rnt_req_rejected', 'name' => 'Rejected'],
            ['context' => 'rent', 'code' => 'rnt_req_cancelled', 'name' => 'Cancelled'],

            // rents
            ['context' => 'rent_status', 'code' => 'rnt_ongoing', 'name' => 'Ongoing'],
            ['context' => 'rent_status', 'code' => 'rnt_completed', 'name' => 'Completed'],
            ['context' => 'rent_status', 'code' => 'rnt_cancelled', 'name' => 'Cancelled'],

            // messages
            ['context' => 'rent_message', 'code' => 'msg_proposal', 'name' => 'Proposal'],
            ['context' => 'rent_message', 'code' => 'msg_response', 'name' => 'Response'],
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
