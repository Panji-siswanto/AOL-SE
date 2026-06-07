<?php

namespace App\Http\Controllers\Renter;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentRequest;
use App\Models\RentMessage;
use App\Models\RentRequest;
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
            ->with(['space.location', 'status', 'messages.sender'])
            ->latest()
            ->paginate(10);

        return view('renter.rents.index', compact('requests'));
    }

    public function acceptReschedule(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403, 'Unauthorized access.');

        // Fetch based on Status::MSG_RESCHEDULE_PROPOSAL
        $proposalMsg = $rentRequest->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('type_id', Status::MSG_RESCHEDULE_PROPOSAL)
            ->whereHas('reschedule')
            ->latest()
            ->first();

        if (!$proposalMsg) return redirect()->back()->with('error', 'No reschedule proposal found to accept.');

        DB::transaction(function () use ($rentRequest, $proposalMsg) {
            $rentRequest->update([
                'visit_date' => $proposalMsg->reschedule->proposed_visit_date
            ]);

            RentMessage::create([
                'request_id' => $rentRequest->id,
                'sender_id'  => Auth::id(),
                'context'    => Status::MSG_RESCHEDULE_ACCEPTED,
                'message'    => 'I accept the proposed visit date: ' . Carbon::parse($proposalMsg->reschedule->proposed_visit_date)->format('M d, Y'),
            ]);
        });

        return redirect()->back()->with('success', 'You have accepted the proposed visit date.');
    }

    public function rejectReschedule(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403, 'Unauthorized access.');

        $proposalMsg = $rentRequest->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('type_id', Status::MSG_RESCHEDULE_PROPOSAL)
            ->whereHas('reschedule')
            ->latest()
            ->first();

        if (!$proposalMsg) return redirect()->back()->with('error', 'No reschedule proposal found to reject.');

        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'context'    => Status::MSG_RESCHEDULE_REJECTED,
            'message'    => 'I cannot make it on the proposed visit date. Let\'s coordinate another time.',
        ]);

        return redirect()->back()->with('success', 'You have rejected the proposed visit date.');
    }

    public function create(Request $request, Space $space)
    {
        if (!Auth::user()->is_verified) {
            return redirect()->route('verification.index')->with('error', 'You must verify your identity before renting a space.');
        }

        if ($space->status_id !== Status::SPC_AVAILABLE) {
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
                'status_id'   => Status::RNT_REQ_PENDING,
            ]);

            if ($request->filled('note')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'context'    => Status::MSG_APPLICATION,
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