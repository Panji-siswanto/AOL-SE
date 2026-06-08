<?php

namespace App\Http\Controllers\Renter;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentRequest;
use App\Http\Requests\RescheduleRentRequest; // Make sure this is imported!
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
            ->with(['space.location', 'status', 'messages.sender', 'messages.reschedule'])
            ->latest()
            ->paginate(10);

        return view('renter.rents.index', compact('requests'));
    }

    public function acceptReschedule(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403, 'Unauthorized access.');

        $proposalMsg = $rentRequest->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('type_id', Status::where('code', 'msg_reschedule_proposal')->value('id'))
            ->whereHas('reschedule')
            ->latest()
            ->first();

        if (!$proposalMsg) return redirect()->back()->with('error', 'No reschedule proposal found to accept.');

        DB::transaction(function () use ($rentRequest, $proposalMsg) {
            $prop = $proposalMsg->reschedule;
            
            // Recalculate price based on the newly accepted dates!
            $start = Carbon::parse($prop->proposed_start_date);
            $end = Carbon::parse($prop->proposed_end_date);
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
                'message'    => 'I accept the proposed dates. The contract has been updated!',
            ]);
        });

        return redirect()->back()->with('success', 'You have accepted the proposed dates and the contract is updated.');
    }

    public function rejectReschedule(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403, 'Unauthorized access.');

        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'type_id'    => Status::where('code', 'msg_reschedule_rejected')->value('id'),
            'message'    => 'I cannot accommodate the proposed dates. Let us coordinate another time.',
        ]);

        return redirect()->back()->with('success', 'You have rejected the proposed dates.');
    }

    public function proposeReschedule(RescheduleRentRequest $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403, 'Unauthorized access.');

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

        return redirect()->back()->with('success', 'Your counter-proposal has been sent to the owner!');
    }

    public function cancel(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403, 'Unauthorized access.');

        DB::transaction(function () use ($rentRequest) {
            $rentRequest->update([
                'status_id' => Status::where('code', 'rnt_req_cancelled')->value('id')
            ]);

            RentMessage::create([
                'request_id' => $rentRequest->id,
                'sender_id'  => Auth::id(),
                'type_id'    => Status::where('code', 'msg_decline_reason')->value('id'),
                'message'    => 'The renter has cancelled this application.',
            ]);
        });

        return redirect()->back()->with('success', 'Your application has been successfully cancelled.');
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
            $type = strtolower($pricing->pricingType->code);

            if ($type === 'daily') {
                $end->addDays($duration);
            } elseif ($type === 'weekly') {
                $end->addWeeks($duration);
            } elseif ($type === 'monthly') {
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
            return redirect()->route('rents.index')->with('success', 'Your rental request has been successfully sent to the owner!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit request: ' . $e->getMessage())->withInput();
        }
    }
}