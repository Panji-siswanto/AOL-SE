<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreSpaceRegistrationRequest;
use App\Models\Location;
use App\Models\SpaceRegistration;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpaceRegistrationController extends Controller
{
    public function index(Request $request){
        $query = SpaceRegistration::with(['location', 'status'])
            ->where('owner_id', auth()->id); 

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

        return view('space-registration.index', compact('registrations'));
    }

    public function create(){
        
        return view('space-registration.create');

    }

    public function show(Request $request, $id)
    {
        $registration = SpaceRegistration::with(['location', 'status'])
            ->where('owner_id', auth()->id) 
            ->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $registration
            ]);
        }

        // return view('space-registration.show', compact('registration'));
    }

    public function store(StoreSpaceRegistrationRequest $request){
        // get status
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

            // Create reg record
            $registration = SpaceRegistration::create([
                'owner_id' => auth()->id, 
                'location_id' => $location->id,
                'name' => $request->name,
                'description' => $request->description,
                'size' => $request->size,
                'price' => $request->price,
                'status_id' => Status::REG_PENDING,
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