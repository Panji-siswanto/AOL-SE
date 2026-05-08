<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpaceRegistration;
use App\Models\Space;
use App\Models\Status;
use App\Models\RegistrationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListingRequestController extends Controller{

    public function index(Request $request) {

        $requests = SpaceRegistration::with(['location', 'status', 'owner'])
            ->search($request->search)           // Uses scopeSearch()
            ->withStatus($request->status)       // Uses scopeWithStatus()
            
            //apply filter here
            ->when($request->sort_price, function ($query, $direction) {
                // e.g., ?sort_price=asc
                $query->orderBy('price', $direction);
            })
            ->when($request->sort_date, function ($query, $direction) {
                // e.g., ?sort_date=oldest
                $direction = $direction === 'oldest' ? 'asc' : 'desc';
                $query->orderBy('created_at', $direction);
            },
            function ($query) {
                $query->latest(); 
            })
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $requests
            ]);
        }

        return view('admin.listing-requests.index', compact('requests'));
    }

    public function show(Request $request, SpaceRegistration $registration){
        $registration->load(['location', 'owner']);
        return response()->json([
            'status' => 'success',
            'data' => $registration
        ]);
    }

    public function approve(Request $request, SpaceRegistration $registration){
        $request->validate([
            'note' => 'required|string'
        ]);

        $approvedRegStatus = Status::where('context', 'registration')->where('code', 'reg_approved')->firstOrFail();
        $availableSpaceStatus = Status::where('context', 'spaces')->where('code', 'spc_available')->firstOrFail();

        DB::beginTransaction();

        try {
            $registration->update(['status_id' => $approvedRegStatus->id]);

            $space = Space::create([
                'owner_id' => $registration->owner_id,
                'location_id' => $registration->location_id,
                'registration_id' => $registration->id,
                'name' => $registration->name,
                'description' => $registration->description,
                'size' => $registration->size,
                'price' => $registration->price,
                'status_id' => $availableSpaceStatus->id,
            ]);

            $user = User::findOrFail($registration->owner_id);
            if (!$user->hasRole('owner')) {
                $user->assignRole('owner');
            }

            RegistrationLog::create([
                'registration_id' => $registration->id,
                'admin_id' => 1, //hardcoded for testing
                'note' => $request->note,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success', 
                'message' => 'Listing approved.', 
                'data' => $space
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, SpaceRegistration $registration){

        $request->validate([
            'note' => 'required|string'
        ]);

        $rejectedRegStatus = Status::where('context', 'registration')->where('code', 'reg_rejected')->firstOrFail();
        DB::beginTransaction();

        try {
            $registration->update(['status_id' => $rejectedRegStatus->id]);
            RegistrationLog::create([
                'registration_id' => $registration->id,
                'admin_id' => 1, //hardcoed for testing
                'note' => $request->note, 
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success', 
                'message' => 'Listing rejected.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}