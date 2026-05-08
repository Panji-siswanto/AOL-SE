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

  public function index(Request $request)
    {
        $requests = SpaceRegistration::with(['location', 'status', 'owner'])
            ->where('status_id', Status::REG_PENDING)
            ->search($request->search)
            ->when($request->sort_price, function ($query, $direction) {
                $query->orderBy('price', $direction);
            })
            ->latest()
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

        DB::beginTransaction();
        try {
            $registration->update(['status_id' => Status::REG_APPROVED]);

            $space = Space::create([
                'owner_id' => $registration->owner_id,
                'location_id' => $registration->location_id,
                'registration_id' => $registration->id,
                'name' => $registration->name,
                'description' => $registration->description,
                'size' => $registration->size,
                'price' => $registration->price,
                'status_id' => Status::SPC_AVAILABLE,
            ]);

            $user = User::findOrFail($registration->owner_id);
            if (!$user->hasRole('owner')) {
                $user->assignRole('owner');
            }

            RegistrationLog::create([
                'registration_id' => $registration->id,
                'admin_id' => auth()->id,
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
        DB::beginTransaction();

        try {
            $registration->update(['status_id' => Status::REG_REJECTED]);
            RegistrationLog::create([
                'registration_id' => $registration->id,
                'admin_id' => auth()->id,
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