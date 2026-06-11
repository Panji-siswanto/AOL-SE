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
use App\Models\Payment; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RentRequestController extends Controller
{
    public function index(Request $request) 
    {
        $query = Auth::user()->rentRequests()
            ->with(['space.location', 'space.registration', 'status', 'messages.sender', 'reschedules']);

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

        return view('renter.rents.index', compact('requests'));
    }

    public function create(Request $request, Space $space)
    {
        if (!Auth::user()->is_verified) return redirect()->route('verification.index')->with('error', 'You must verify your identity before renting a space.');
        if ($space->status_id !== Status::where('code', 'spc_available')->value('id')) return redirect()->back()->with('error', 'This space is not currently available for rent.');
        if ($space->owner_id === Auth::id()) return redirect()->back()->with('error', 'You cannot rent your own space.');
        if ($space->has_active_request) return redirect()->route('rents.index')->with('error', 'You already have an active or pending request for this space.');

        return view('renter.rents.create', compact('space')); 
    }

    public function store(StoreRentRequest $request, Space $space)
    {
        try {
            DB::beginTransaction();

            $totalDays = (int) $request->duration; 
            $start = Carbon::parse($request->start_date);
            $end = clone $start;
            $end->addDays($totalDays);

            $rates = SpaceRegistrationPrice::where('space_registration_id', $space->registration_id)
                ->join('pricing_types', 'space_registration_prices.pricing_type_id', '=', 'pricing_types.id')
                ->pluck('space_registration_prices.price', 'pricing_types.code')
                ->mapWithKeys(fn($item, $key) => [strtolower($key) => $item]);

            $rem = $totalDays;
            $totalPrice = 0;
            $breakdown = [];

            if (isset($rates['monthly'])) {
                $m = floor($rem / 30);
                if ($m > 0) {
                    $sub = $m * $rates['monthly'];
                    $totalPrice += $sub;
                    $rem %= 30;
                    $breakdown['monthly'] = ['qty' => $m, 'unit_price' => $rates['monthly'], 'subtotal' => $sub];
                }
            }
            if (isset($rates['weekly'])) {
                $w = floor($rem / 7);
                if ($w > 0) {
                    $sub = $w * $rates['weekly'];
                    $totalPrice += $sub;
                    $rem %= 7;
                    $breakdown['weekly'] = ['qty' => $w, 'unit_price' => $rates['weekly'], 'subtotal' => $sub];
                }
            }
            if (isset($rates['daily']) && $rem > 0) {
                $sub = $rem * $rates['daily'];
                $totalPrice += $sub;
                $breakdown['daily'] = ['qty' => $rem, 'unit_price' => $rates['daily'], 'subtotal' => $sub];
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

            $rentRequest = RentRequest::create([
                'renter_id'       => Auth::id(),
                'space_id'        => $space->id,
                'start_date'      => $start->toDateString(),
                'end_date'        => $end->toDateString(),
                'visit_date'      => $request->visit_date,
                'total_price'     => round($totalPrice),
                'price_breakdown' => $breakdown,
                'status_id'       => Status::where('code', 'rnt_req_pending')->value('id'),
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
            $latestReschedule = $rentRequest->reschedules()->latest()->first();
            $awaitingPaymentId = Status::where('code', 'rnt_awaiting_payment')->value('id');  
            
            if ($latestReschedule) {
                $rentRequest->update([
                    'visit_date'      => $latestReschedule->proposed_visit_date,
                    'start_date'      => $latestReschedule->proposed_start_date,
                    'end_date'        => $latestReschedule->proposed_end_date,
                    'total_price'     => $latestReschedule->proposed_total_price,
                    'price_breakdown' => $latestReschedule->price_breakdown,
                    'status_id'       => $awaitingPaymentId 
                ]);
            } else {
                $rentRequest->update(['status_id' => $awaitingPaymentId]); 
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

        return redirect()->back()->with('success', 'Offer accepted! Please complete your payment to activate the contract.');
    }

    public function pay(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403);
        $awaitingPaymentId = Status::where('code', 'rnt_awaiting_payment')->value('id');
        if ($rentRequest->status_id !== $awaitingPaymentId) abort(400, 'Invalid payment state.');

        DB::transaction(function () use ($rentRequest) {
            $ongoingId = Status::where('code', 'rnt_ongoing')->value('id');
            
            $rentRequest->update(['status_id' => $ongoingId]);

            Payment::create([
                'rent_request_id' => $rentRequest->id,
                'amount'          => $rentRequest->total_price,
                'method'          => 'Simulated Demo Gateway',
                'paid_at'         => now(),
            ]);

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
                'status_id'       => $ongoingId,
            ]);
        });

        return redirect()->back()->with('success', 'Payment successful! Your rent is now active.');
    }

    public function reject(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403);

        DB::transaction(function () use ($rentRequest, $request) {
            $rentRequest->update(['status_id' => Status::where('code', 'rnt_req_cancelled')->value('id')]);

            if ($request->filled('reject_reason')) {
                RentMessage::create([
                    'request_id' => $rentRequest->id,
                    'sender_id'  => Auth::id(),
                    'type_id'    => Status::where('code', 'msg_decline_reason')->value('id'),
                    'message'    => $request->reject_reason,
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

        return redirect()->back()->with('success', 'Counter-proposal sent! Pending owner approval.');
    }

    public function requestFinish(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403);
        
        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'type_id'    => Status::where('code', 'msg_finish_request')->value('id'),
            'message'    => $request->finish_reason,
        ]);
        return back()->with('success', 'Early finish request sent to owner.');
    }

    public function approveFinish(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403);

        $completedId = Status::where('code', 'rnt_completed')->value('id');
        $rentRequest->update(['status_id' => $completedId]);
        if ($rentRequest->rent) $rentRequest->rent->update(['status_id' => $completedId]);

        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'type_id'    => Status::where('code', 'msg_finish_accepted')->value('id'),
            'message'    => 'Early finish request approved. Contract is now completed.',
        ]);
        return back()->with('success', 'Rent marked as completed.');
    }

    public function rejectFinish(Request $request, RentRequest $rentRequest)
    {
        if ($rentRequest->renter_id !== Auth::id()) abort(403);
        
        RentMessage::create([
            'request_id' => $rentRequest->id,
            'sender_id'  => Auth::id(),
            'type_id'    => Status::where('code', 'msg_finish_rejected')->value('id'),
            'message'    => $request->reject_reason,
        ]);
        return back()->with('success', 'Early finish request rejected.');
    }
}