<?php

namespace App\Http\Controllers\Renter;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentRequest;
use App\Http\Requests\RescheduleRentRequest;
use App\Models\Rent;
use App\Models\RentMessage;
use App\Models\RentRequest;
use App\Models\RentReschedule;
use App\Models\Space;
use App\Models\SpaceRegistrationPrice;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RentRequestController extends Controller
{
    public function index()
    {
        $requests = Auth::user()->rentRequests()
            ->with(['space.location', 'status', 'messages.sender', 'reschedules'])
            ->latest()
            ->paginate(10);

        return view('renter.rents.index', compact('requests'));
    }

    public function create(Request $request, Space $space)
    {
        if (!Auth::user()->is_verified) {
            return redirect()->route('verification.index')->with('error', 'You must verify your identity before renting a space.');
        }

        if ($space->status_id !== Status::where('code', 'spc_available')->value('id')) {
            return redirect()->back()->with('error', 'This space is not currently available for rent.');
        }

        if ($space->owner_id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot rent your own space.');
        }

        if ($space->has_active_request) {
            return redirect()->route('rents.index')->with('error', 'You already have an active or pending request for this space.');
        }

        $selectedPricing = $space->registration->prices()->with('pricingType')->find($request->pricing_id);
        
        if (!$selectedPricing) return redirect()->route('spaces.show', $space->id)->with('error', 'Please select a valid rental package first.');

        return view('renter.rents.create', compact('space', 'selectedPricing'));
    }

    public function store(StoreRentRequest $request, Space $space)
    {
        try {
            DB::beginTransaction();

            $duration = (int) $request->duration;

            $pricing = SpaceRegistrationPrice::with('pricingType')->findOrFail($request->pricing_id);
            
            $totalPrice = $pricing->price * $duration; 
            
            $start = Carbon::parse($request->start_date);
            $end = clone $start;
            $typeCode = strtolower($pricing->pricingType->code);

            if ($typeCode === 'daily') {
                $end->addDays($duration);
            } elseif ($typeCode === 'weekly') {
                $end->addWeeks($duration);
            } elseif ($typeCode === 'monthly') {
                $end->addMonths($duration);
            }

            $rentRequest = RentRequest::create([
                'renter_id'   => Auth::id(),
                'space_id'    => $space->id,
                'pricing_id'  => $pricing->id,
                'start_date'  => $start->toDateString(),
                'end_date'    => $end->toDateString(),
                'visit_date'  => $request->visit_date,
                'total_price' => $totalPrice,
                'status_id'   => Status::where('code', 'rnt_req_pending')->value('id'),
            ]);

            if ($request->filled('note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_application')->value('id'),
                    'message'    => $request->note,
                ]);
            }
        
            DB::commit();
            return redirect()->route('rents.index')->with('success', 'Rent request submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit request: ' . $e->getMessage())->withInput();
        }
    }

    public function approve(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403);

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

            // Only create message if the renter explicitly left a note
            if ($request->filled('response_note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_approval_note')->value('id'),
                    'message'    => $request->response_note,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Application accepted! Contract created.');
    }

    public function reject(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403);

        DB::transaction(function () use ($rentRequest, $request) {
            $rentRequest->update(['status_id' => Status::where('code', 'rnt_req_cancelled')->value('id')]);

            // Only create message if the renter explicitly left a reason
            if ($request->filled('response_note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_decline_reason')->value('id'),
                    'message'    => $request->response_note,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Application successfully cancelled.');
    }

    public function reschedule(RescheduleRentRequest $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403);

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

            // Only create message if the renter explicitly left a note
            if ($request->filled('response_note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_reschedule_proposal')->value('id'),
                    'message'    => $request->response_note,
                ]);
            }

            // Create independent Reschedule Log (Option B Schema!)
            RentReschedule::create([
                'rent_request_id'     => $rentRequest->id,
                'sender_id'           => Auth::id(),
                'proposed_visit_date' => $request->new_visit_date,
                'proposed_start_date' => $start->toDateString(),
                'proposed_end_date'   => $end->toDateString(),
            ]);
        });

        return redirect()->back()->with('success', 'Counter-proposal sent! The dates are temporarily updated pending owner approval.');
    }
}