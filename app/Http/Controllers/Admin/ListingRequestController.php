<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpaceRegistration;
use App\Models\Space;
use App\Models\Status;
use App\Models\RegistrationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListingRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = SpaceRegistration::with(['location', 'status', 'owner'])
            ->where('status_id', Status::REG_PENDING)
            // Optional scopes: only trigger if methods exist on model
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
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

        // Returns view pointing to singular folder mapping
        return view('admin.listing-request.index', compact('requests'));
    }

    public function show(Request $request, SpaceRegistration $registration)
    {
        $registration->load(['location', 'owner']);
        return response()->json([
            'status' => 'success',
            'data' => $registration
        ]);
    }

    public function approve(Request $request, SpaceRegistration $registration)
    {
        $owner = $registration->owner;
        if ($owner->ver_status !== Status::USR_VERIFIED) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot approve listing. The owner\'s identity (KTP) verification must be approved first.'
                ], 400);
            }
            return redirect()->back()->withErrors(['Verification Error' => 'Cannot approve listing. The owner\'s identity (KTP) verification must be approved first.']);
        }

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
                'admin_id' => Auth::id(), // Fixed missing parentheses
                'note' => $request->note ?? 'Space listing formally approved and published to catalog.',
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Listing approved successfully.', 
                    'data' => $space
                ], 200);
            }

            return redirect()->back()->with('success', "Space listing '{$space->name}' has been approved and published.");

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            return redirect()->back()->withErrors(['System Error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, SpaceRegistration $registration)
    {
        $request->validate(['note' => 'required|string|max:500']);
        
        DB::beginTransaction();
        try {
            $registration->update(['status_id' => Status::REG_REJECTED]);
            
            RegistrationLog::create([
                'registration_id' => $registration->id,
                'admin_id' => Auth::id(), // Fixed missing parentheses
                'note' => $request->note, 
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['status' => 'success', 'message' => 'Listing rejected.'], 200);
            }

            return redirect()->back()->with('success', 'Space listing application has been rejected.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            return redirect()->back()->withErrors(['System Error' => $e->getMessage()]);
        }
    }
}