<?php

namespace App\Http\Controllers\Renter;

use App\Http\Controllers\Controller;
use App\Models\RentMessage;
use App\Models\RentRequest;
use App\Models\Space;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RentRequestController extends Controller
{
    public function index(){
        $requests = Auth::user()->rentRequests()
            ->with(['space.location', 'status', 'messages.sender'])
            ->latest()
            ->paginate(10);

        return view('renter.rents.index', compact('requests'));
    }

    public function acceptReschedule(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this reservation request.');
        }

        $proposal = $rentRequest->messages
            ->where('sender_id', '!=', Auth::id())
            ->whereNotNull('proposed_visit_date')
            ->sortByDesc('created_at')
            ->first();

        if (!$proposal) {
            return redirect()->back()->with('error', 'No reschedule proposal found to accept.');
        }

        $rentRequest->update(['visit_date' => $proposal->proposed_visit_date]);

        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id' => Auth::id(),
            'type_id' => Status::MSG_RESPONSE,
            'note' => 'Renter accepted the proposed visit date: ' . $proposal->proposed_visit_date,
        ]);

        return redirect()->back()->with('success', 'You have accepted the proposed visit date.');
    }

    public function rejectReschedule(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this reservation request.');
        }

        $proposal = $rentRequest->messages
            ->where('sender_id', '!=', Auth::id())
            ->whereNotNull('proposed_visit_date')
            ->sortByDesc('created_at')
            ->first();

        if (!$proposal) {
            return redirect()->back()->with('error', 'No reschedule proposal found to reject.');
        }

        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id' => Auth::id(),
            'type_id' => Status::MSG_RESPONSE,
            'note' => 'Renter rejected the proposed visit date: ' . $proposal->proposed_visit_date,
        ]);

        return redirect()->back()->with('success', 'You have rejected the proposed visit date.');
    }

    public function create(Request $request, Space $space){
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

        // Fetch the exact pricing package they selected
        $selectedPricing = $space->registration->prices()->with('pricingType')->find($request->pricing_id);
        
        if (!$selectedPricing) {
            return redirect()->route('spaces.show', $space->id)->with('error', 'Please select a valid rental package first.');
        }

        return view('renter.rents.create', compact('space', 'selectedPricing'));
    }

    public function store(Request $request, Space $space){
        if (!Auth::user()->is_verified) {
            return redirect()->route('verification.index')->with('error', 'You must verify your identity before renting a space.');
        }

        $request->validate([
            'pricing_id' => ['required', 'exists:space_registration_prices,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'duration'   => ['required', 'integer', 'min:1'],
            'visit_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:start_date'],
            'note'       => ['required', 'string', 'max:1000'],
        ]);

        if ($space->has_active_request) {
            return redirect()->route('rents.index')->with('error', 'You already have an active application here.');
        }

        try {
            DB::beginTransaction();

            $pricing = $space->registration->prices()->with('pricingType')->findOrFail($request->pricing_id);
            $typeCode = $pricing->pricingType->code;

            $start = \Carbon\Carbon::parse($request->start_date);
            $end = clone $start;
            
            $duration = (int) $request->duration; 

            if ($typeCode === 'daily') {
                $end->addDays($duration);
            } elseif ($typeCode === 'weekly') {
                $end->addWeeks($duration);
            } elseif ($typeCode === 'monthly') {
                $end->addMonths($duration);
            }

            $totalPrice = $pricing->price * $duration;

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

            RentMessage::create([
                'request_id' => $rentRequest->id,
                'sender_id'  => Auth::id(),
                'type_id'    => Status::MSG_PROPOSAL,
                'note'       => $request->note,
            ]);

            DB::commit();
            return redirect()->route('rents.index')->with('success', 'Rent request submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit request: ' . $e->getMessage())->withInput();
        }
    }
}