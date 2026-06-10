<?php

namespace App\Console\Commands;

use App\Models\RentRequest;
use App\Models\Rent;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompleteExpiredRents extends Command
{
    protected $signature = 'rents:complete';
    protected $description = 'Automatically mark ongoing rents as completed if their end_date has passed.';

    public function handle()
    {
        $ongoingId = Status::where('code', 'rnt_ongoing')->value('id');
        $completedId = Status::where('code', 'rnt_completed')->value('id');

        $expiredRequests = RentRequest::where('status_id', $ongoingId)
            ->whereDate('end_date', '<', Carbon::today())
            ->get();

        $count = 0;
        foreach ($expiredRequests as $request) {
            $request->update(['status_id' => $completedId]);
            
            if ($request->rent) {
                $request->rent->update(['status_id' => $completedId]);
            }
            $count++;
        }

        $this->info("Successfully completed {$count} expired rent(s).");
    }
}