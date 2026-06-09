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
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RentManagementController extends Controller
{
    public function index()
    {
        $requests = RentRequest::whereHas('space', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->with(['renter', 'space.location', 'status', 'pricing.pricingType'])
            ->latest()
            ->paginate(10);
            
        return view('owner.rents.index', compact('requests'));
    }

    public function show(RentRequest $rentRequest)
    {
        if ($rentRequest->space->owner_id !== Auth::id()) abort(403);
        $rentRequest->load(['renter', 'space.location', 'status', 'pricing.pricingType', 'messages.sender', 'reschedules']);
        return view('owner.rents.show', compact('rentRequest'));
    }

    public function approve(ApproveRentRequest $request, RentRequest $rentRequest)
    {
        DB::transaction(function () use ($rentRequest, $request) {
            $rentRequest->update(['status_id' => Status::where('code', 'rnt_req_accepted')->value('id')]);

            if (!$rentRequest->rent) {
                $space = $rentRequest->space;
                Rent::create([
                    'request_id'      => $rentRequest->id,
                    'space_id'        => $space->id,
                    'space_name'      => $space->name,
                    'price'           => $rentRequest->pricing->price ?? 0,
                    'pricing_type'    => $rentRequest->pricing->pricingType->code ?? 'base',
                    'space_length'    => $space->length,
                    'space_width'     => $space->width,
                    'space_area'      => $space->area,
                    'space_address'   => $space->location->address . ', ' . $space->location->city,
                    'space_latitude'  => $space->location->latitude,
                    'space_longitude' => $space->location->longitude,
                    'renter_id'       => $rentRequest->renter_id,
                    'start_date'      => $rentRequest->start_date,
                    'end_date'        => $rentRequest->end_date,
                    'status_id'       => Status::where('code', 'rnt_ongoing')->value('id'),
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

        return redirect()->route('owner.reservations.show', $rentRequest->id)->with('success', 'Application accepted! Contract created.');
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
            
            $basePrice = $rentRequest->pricing->price;
            $code = strtolower($rentRequest->pricing->pricingType->code);
            
            if ($code === 'weekly') $totalPrice = ($basePrice / 7) * $totalDays;
            elseif ($code === 'monthly') $totalPrice = ($basePrice / 30) * $totalDays;
            else $totalPrice = $basePrice * $totalDays;

            $rentRequest->update([
                'visit_date'  => $request->new_visit_date,
                'start_date'  => $start->toDateString(),
                'end_date'    => $end->toDateString(),
                'total_price' => round($totalPrice),
            ]);

            // Always log the reschedule proposal event
            if ($request->filled('response_note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_reschedule_proposal')->value('id'),
                    'message'    => $request->response_note,
                ]);
            }

            RentReschedule::create([
                'rent_request_id'     => $rentRequest->id,
                'sender_id'           => Auth::id(),
                'proposed_visit_date' => $request->new_visit_date,
                'proposed_start_date' => $start->toDateString(),
                'proposed_end_date'   => $end->toDateString(),
            ]);
        });
        return redirect()->back()->with('success', 'Dates proposed!');
    }
}