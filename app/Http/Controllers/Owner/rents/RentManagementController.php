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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        if ($rentRequest->space->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this reservation request.');
        }

        $rentRequest->load(['renter', 'space.location', 'status', 'pricing.pricingType', 'messages.reschedule']);

        return view('owner.rents.show', compact('rentRequest'));
    }

    public function approve(ApproveRentRequest $request, RentRequest $rentRequest)
    {
        DB::transaction(function () use ($rentRequest, $request) {
            $rentRequest->update(['status_id' => Status::RNT_REQ_ACCEPTED]);

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
                    'status_id'       => Status::RNT_ONGOING,
                ]);
            }

            if ($request->filled('response_note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    // Safely fetch the integer ID instead of passing a string
                    'type_id'    => Status::where('code', 'msg_approval_note')->value('id'),
                    'message'    => $request->response_note,
                ]);
            }
        });

        return redirect()->route('owner.reservations.show', $rentRequest->id)->with('success', 'Rent request has been accepted and contract created.');
    }

    public function reject(RejectRentRequest $request, RentRequest $rentRequest)
    {
        DB::transaction(function () use ($rentRequest, $request) {
            $rentRequest->update(['status_id' => Status::RNT_REQ_REJECTED]);
            
            // Safely fetch the correct ID for decline reason
            $declineMessageTypeId = Status::where('code', 'msg_decline_reason')->value('id');
            
            RentMessage::create([
                'request_id' => $rentRequest->id,
                'sender_id'  => Auth::id(),
                'type_id'    => $declineMessageTypeId,
                'message'    => $request->reject_reason,
            ]);
        });

        return redirect()->route('owner.reservations.index')->with('success', 'Rent request has been rejected.');
    }

 // Update the existing reschedule method
    public function reschedule(RescheduleRentRequest $request, RentRequest $rentRequest)
    {
        DB::transaction(function () use ($rentRequest, $request) {
            $message = RentMessage::create([
                'request_id' => $rentRequest->id,
                'sender_id'  => Auth::id(),
                'type_id'    => Status::where('code', 'msg_reschedule_proposal')->value('id'),
                'message' => $request->response_note ?? 'I have proposed new dates for this reservation.',            
        ]);

            RentReschedule::create([
                'rent_message_id'     => $message->id,
                'proposed_visit_date' => $request->new_visit_date,
                'proposed_start_date' => $request->new_start_date,
                'proposed_end_date'   => $request->new_end_date,
            ]);
        });

        return redirect()->back()->with('success', 'New dates proposed successfully!');
    }

    // Add these two NEW methods below it
    public function acceptReschedule(Request $request, RentRequest $rentRequest)
    {
        $proposalMsg = $rentRequest->messages()->where('sender_id', '!=', Auth::id())
            ->where('type_id', Status::where('code', 'msg_reschedule_proposal')->value('id'))
            ->whereHas('reschedule')->latest()->first();

        if (!$proposalMsg) return redirect()->back()->with('error', 'No proposal found.');

        DB::transaction(function () use ($rentRequest, $proposalMsg) {
            $prop = $proposalMsg->reschedule;
            
            // Recalculate the price based on new dates
            $start = \Carbon\Carbon::parse($prop->proposed_start_date);
            $end = \Carbon\Carbon::parse($prop->proposed_end_date);
            $code = strtolower($rentRequest->pricing->pricingType->code);
            
            $duration = 1;
            if ($code === 'daily') $duration = $start->diffInDays($end) ?: 1;
            if ($code === 'weekly') $duration = $start->diffInWeeks($end) ?: 1;
            if ($code === 'monthly') $duration = $start->diffInMonths($end) ?: 1;

            $rentRequest->update([
                'visit_date'  => $prop->proposed_visit_date,
                'start_date'  => $prop->proposed_start_date,
                'end_date'    => $prop->proposed_end_date,
                'total_price' => $duration * $rentRequest->pricing->price,
            ]);

            RentMessage::create([
                'request_id' => $rentRequest->id,
                'sender_id'  => Auth::id(),
                'type_id'    => Status::where('code', 'msg_reschedule_accepted')->value('id'),
                'message' => $request->response_note ?? 'I accept your counter-proposal. The dates and prices have been updated.',            
            ]);
        });

        return redirect()->back()->with('success', 'Counter-proposal accepted! Dates and total price have been updated.');
    }

    public function rejectReschedule(Request $request, RentRequest $rentRequest)
    {
        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'type_id'    => Status::where('code', 'msg_reschedule_rejected')->value('id'),
            'message' => $request->response_note ?? 'I cannot accommodate the proposed dates',            
        ]);

        return redirect()->back()->with('success', 'You rejected the counter-proposal.');
    }
}