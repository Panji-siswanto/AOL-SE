<?php

namespace App\Http\Controllers\Owner\rents;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveRentRequest;
use App\Http\Requests\RejectRentRequest;
use App\Http\Requests\RescheduleRentRequest;
use App\Models\Rent;
use App\Models\RentMessage;
use App\Models\RentRequest;
use App\Models\RentReschedule;
use App\Models\SpaceRegistrationPrice;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RentManagementController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = RentRequest::whereHas('space', function ($q) {
                $q->where('owner_id', Auth::id());
            })
            ->with(['renter', 'space.location', 'space.registration', 'status', 'messages.sender', 'reschedules']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $statusCode = $request->input('status', 'all'); 
        if ($statusCode !== 'all') {
            $query->withStatus($statusCode);
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'price_high') {
            $query->orderBy('total_price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('total_price', 'asc');
        } else {
            $query->latest();
        }

        $requests = $query->paginate(10)->withQueryString();
            
        return view('owner.rents.index', compact('requests'));
    }


    public function show(RentRequest $rentRequest)
    {
        if ($rentRequest->space->owner_id !== Auth::id()) abort(403);
        $rentRequest->load(['renter', 'space.location', 'space.registration', 'status', 'messages.sender', 'reschedules']);
        return view('owner.rents.show', compact('rentRequest'));
    }

  public function approve(ApproveRentRequest $request, RentRequest $rentRequest)
    {
        DB::transaction(function () use ($rentRequest, $request) {
            
            $latestReschedule = $rentRequest->reschedules()->latest()->first();
            $ongoingStatusId = Status::where('code', 'rnt_ongoing')->value('id'); 
            
            if ($latestReschedule) {
                $rentRequest->update([
                    'visit_date'      => $latestReschedule->proposed_visit_date,
                    'start_date'      => $latestReschedule->proposed_start_date,
                    'end_date'        => $latestReschedule->proposed_end_date,
                    'total_price'     => $latestReschedule->proposed_total_price,
                    'price_breakdown' => $latestReschedule->price_breakdown,
                    'status_id'       => $ongoingStatusId 
                ]);
            } else {
                $rentRequest->update(['status_id' => $ongoingStatusId]); 
            }

            if (!$rentRequest->rent) {
                $space = $rentRequest->space;
                Rent::create([
                    'request_id'      => $rentRequest->id,
                    'space_id'        => $space->id,
                    'space_name'      => $space->name,
                    'price'           => $rentRequest->total_price,
                    'pricing_type'    => 'dynamic_combination',
                    'space_length'    => $space->length,
                    'space_width'     => $space->width,
                    'space_area'      => $space->area,
                    'space_address'   => $space->location->address . ', ' . $space->location->city,
                    'space_latitude'  => $space->location->latitude,
                    'space_longitude' => $space->location->longitude,
                    'renter_id'       => $rentRequest->renter_id,
                    'start_date'      => $rentRequest->start_date,
                    'end_date'        => $rentRequest->end_date,
                    'status_id'       => $ongoingStatusId, 
                ]);
            }

            if ($request->filled('response_note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_approval_note')->value('id'),
                    'message'    => $request->response_note,
                ]);
            }
        });

        return redirect()->route('owner.reservations.index')->with('success', 'Application accepted! Contract is now active.');
    }
    public function reject(RejectRentRequest $request, RentRequest $rentRequest)
    {
        DB::transaction(function () use ($rentRequest, $request) {
            $rentRequest->update(['status_id' => Status::where('code', 'rnt_req_rejected')->value('id')]);
            
            if ($request->filled('reject_reason')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_decline_reason')->value('id'),
                    'message'    => $request->reject_reason,
                ]);
            }
        });
        return redirect()->route('owner.reservations.index')->with('success', 'Application rejected.');
    }

    public function reschedule(RescheduleRentRequest $request, RentRequest $rentRequest)
    {
        DB::transaction(function () use ($rentRequest, $request) {
            $start = Carbon::parse($request->new_start_date);
            $end = Carbon::parse($request->new_end_date);
            $totalDays = $start->diffInDays($end) ?: 1;

            $rates = SpaceRegistrationPrice::where('space_registration_id', $rentRequest->space->registration_id)
                ->join('pricing_types', 'space_registration_prices.pricing_type_id', '=', 'pricing_types.id')
                ->pluck('space_registration_prices.price', 'pricing_types.code')
                ->mapWithKeys(fn($item, $key) => [strtolower($key) => $item]);

            $rem = $totalDays;
            $totalPrice = 0;
            $breakdown = [];

            if (isset($rates['monthly'])) {
                $m = floor($rem / 30);
                if ($m > 0) {
                    $totalPrice += $m * $rates['monthly'];
                    $rem %= 30;
                    $breakdown['monthly'] = ['qty' => $m, 'unit_price' => $rates['monthly'], 'subtotal' => $m * $rates['monthly']];
                }
            }
            if (isset($rates['weekly'])) {
                $w = floor($rem / 7);
                if ($w > 0) {
                    $totalPrice += $w * $rates['weekly'];
                    $rem %= 7;
                    $breakdown['weekly'] = ['qty' => $w, 'unit_price' => $rates['weekly'], 'subtotal' => $w * $rates['weekly']];
                }
            }
            if (isset($rates['daily']) && $rem > 0) {
                $totalPrice += $rem * $rates['daily'];
                $breakdown['daily'] = ['qty' => $rem, 'unit_price' => $rates['daily'], 'subtotal' => $rem * $rates['daily']];
                $rem = 0;
            } 
            if ($rem > 0) {
                $smallestRate = $rates['daily'] ?? ($rates['weekly'] ? $rates['weekly'] / 7 : ($rates['monthly'] ? $rates['monthly'] / 30 : 0));
                if ($smallestRate > 0) {
                    $sub = $rem * $smallestRate;
                    $totalPrice += $sub;
                    $breakdown['prorated_days'] = ['qty' => $rem, 'unit_price' => round($smallestRate), 'subtotal' => round($sub)];
                }
            }
            
            $breakdown['summary'] = ['total_days' => $totalDays, 'final_price' => round($totalPrice)];

            if ($request->filled('response_note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_reschedule_proposal')->value('id'),
                    'message'    => $request->response_note,
                ]);
            }

            RentReschedule::create([
                'rent_request_id'      => $rentRequest->id,
                'sender_id'            => Auth::id(), 
                'proposed_visit_date'  => $request->new_visit_date,
                'proposed_start_date'  => $start->toDateString(),
                'proposed_end_date'    => $end->toDateString(),
                'proposed_total_price' => round($totalPrice),
                'price_breakdown'      => $breakdown,
            ]);
        });
        return redirect()->back()->with('success', 'Dates proposed!');
    }

    public function requestFinish(\Illuminate\Http\Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->space->owner_id !== Auth::id()) abort(403);
        
        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'type_id'    => \App\Models\Status::where('code', 'msg_finish_request')->value('id'),
            'message'    => $request->finish_reason,
        ]);
        
        return back()->with('success', 'Early finish request sent to renter.');
    }

    public function approveFinish(\Illuminate\Http\Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->space->owner_id !== Auth::id()) abort(403);

        $completedId = \App\Models\Status::where('code', 'rnt_completed')->value('id');
        
        $rentRequest->update(['status_id' => $completedId]);
        if ($rentRequest->rent) {
            $rentRequest->rent->update(['status_id' => $completedId]);
        }

        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'type_id'    => \App\Models\Status::where('code', 'msg_finish_accepted')->value('id'),
            'message'    => 'Early finish request approved. Contract is now completed.',
        ]);
        
        return back()->with('success', 'Rent marked as completed.');
    }

    public function rejectFinish(\Illuminate\Http\Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->space->owner_id !== Auth::id()) abort(403);
        
        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'type_id'    => \App\Models\Status::where('code', 'msg_finish_rejected')->value('id'),
            'message'    => $request->reject_reason,
        ]);
        
        return back()->with('success', 'Early finish request rejected.');
    }
}