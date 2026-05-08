<?php

namespace App\Http\Controllers\Renter;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaceRegistrationRequest;
use App\Models\Location;
use App\Models\SpaceRegistration;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpaceRegistrationController extends Controller
{
    public function index(Request $request)
    {
        //Start the query, locked ONLY to the current user (ID 1 for Postman testing)
        $query = SpaceRegistration::with(['location', 'status'])
            ->where('owner_id', 1); // hardcoded for testing, Change to auth()->id later

        // apply filter if user provide status (e.g., ?status=reg_approved)
        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('code', $request->status);
            });
        }

        // Sort by date (default to 'desc' so newest is at the top)
        $sortOrder = $request->input('sort', 'desc'); 
        $query->orderBy('created_at', $sortOrder);

        //Execute the query
        $registrations = $query->get();

        //response json
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $registrations
            ]);
        }

        return view('register-space.index', compact('registrations'));
    }

    public function show(Request $request, $id)
    {
        $registration = SpaceRegistration::with(['location', 'status'])
            ->where('owner_id', 1) // hardcoded for testing, Change to auth()->id later
            ->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $registration
            ]);
        }

        // Future UI view
        return view('renter.register-space.show', compact('registration'));
    }

    public function store(StoreSpaceRegistrationRequest $request){
        // get status
        $pendingStatus = Status::where('context', 'registration')
            ->where('code', 'reg_pending')
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // create loc record
            $location = Location::create([
                'city' => $request->city,
                'province' => $request->province,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // 3. Create reg record
            $registration = SpaceRegistration::create([
                'owner_id' => 1, //nanti di ganti "auth()->id", masih testing pake postman jadi static dulu
                'location_id' => $location->id,
                'name' => $request->name,
                'description' => $request->description,
                'size' => $request->size,
                'price' => $request->price,
                'status_id' => $pendingStatus->id,
            ]);

            DB::commit();

            // buat test api
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Space registration submitted successfully.',
                    'data' => $registration
                ], 201);
            }

            // 5. redirect, nanti ganti route nya
            return redirect()->route('dashboard')->with('success', 'Space registration submitted! Waiting for admin approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to submit space registration.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    
}