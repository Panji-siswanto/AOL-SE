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
                    'context'    => 'approval_note',
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

            RentMessage::create([
                'request_id' => $rentRequest->id,
                'sender_id'  => Auth::id(),
                'context'    => 'decline_reason',
                'message'    => $request->reject_reason,
            ]);
        });

        return redirect()->route('owner.reservations.index')->with('success', 'Rent request has been rejected.');
    }

    public function reschedule(RescheduleRentRequest $request, RentRequest $rentRequest)
    {
        DB::transaction(function () use ($rentRequest, $request) {
            
            $rentRequest->update(['visit_date' => $request->new_visit_date]);

            $message = RentMessage::create([
                'request_id' => $rentRequest->id,
                'sender_id'  => Auth::id(),
                'context'    => 'reschedule_proposal',
                'message'    => $request->response_note,
            ]);

            RentReschedule::create([
                'rent_message_id'     => $message->id,
                'proposed_visit_date' => $request->new_visit_date,
            ]);
        });

        return redirect()->route('owner.reservations.show', $rentRequest->id)->with('success', 'New visit date proposal has been sent.');
    }
}