<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Space;
use App\Models\SpaceRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'live');

        $registrationsQuery = SpaceRegistration::with(['location', 'status'])
            ->where('owner_id', Auth::id());

        if ($activeTab === 'applications') {
            $registrationsQuery->search($request->search)->withStatus($request->status);
            if ($request->sort_date === 'oldest') {
                $registrationsQuery->oldest();
            } else {
                $registrationsQuery->latest();
            }
        } else {
            $registrationsQuery->latest();
        }
        
        $registrations = $registrationsQuery->get();

        $spacesQuery = Space::with(['location', 'status'])
            ->where('owner_id', Auth::id());
            
        if ($activeTab === 'live') {
            if ($request->filled('search')) {
                $search = $request->search;
                $spacesQuery->where('name', 'like', "%{$search}%");
            }
            if ($request->filled('status')) {
                $spacesQuery->whereHas('status', function ($q) use ($request) {
                    $q->where('code', $request->status);
                });
            }
            if ($request->sort_date === 'oldest') {
                $spacesQuery->oldest();
            } else {
                $spacesQuery->latest();
            }
        } else {
            $spacesQuery->latest();
        }

        $spaces = $spacesQuery->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'registrations' => $registrations,
                'spaces' => $spaces
            ]);
        }

        // Points to the unified dashboard view
        return view('owner.spaces.index', compact('registrations', 'spaces', 'activeTab'));
    }

    public function show(Space $space)
    {
        // Security check
        if ($space->owner_id !== Auth::id()) abort(403);
        
        $space->load(['location', 'status', 'registration.prices.pricingType', 'photos']);
        return view('owner.spaces.show', compact('space'));
    }

    public function edit(Space $space)
    {
        if ($space->owner_id !== Auth::id()) abort(403);
        return view('owner.spaces.edit', compact('space'));
    }

    public function update(Request $request, Space $space)
    {
        if ($space->owner_id !== Auth::id()) abort(403);
        
        // TODO: Logic for updating a live space
        return redirect()->route('owner.spaces.show', $space->id)->with('success', 'Space updated.');
    }
}