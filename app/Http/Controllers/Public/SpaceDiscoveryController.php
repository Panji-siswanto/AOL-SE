<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceDiscoveryController extends Controller
{
    public function index(Request $request){
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        $query = Space::with(['location', 'photos', 'registration.photos', 'registration.prices'])
            ->whereHas('status', fn($q) => $q->where('code', 'spc_available'));

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('location', fn($qLoc) => $qLoc->where('city', 'like', "%{$search}%")
                                                             ->orWhere('address', 'like', "%{$search}%"));
            });
        }

        // Numeric Filters
        if ($request->filled('min_price')) $query->where('price', '>=', $request->min_price);
        if ($request->filled('max_price')) $query->where('price', '<=', $request->max_price);
        if ($request->filled('min_area')) $query->where('area', '>=', $request->min_area);
        if ($request->filled('max_area')) $query->where('area', '<=', $request->max_area);

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'area_asc'   => $query->orderBy('area', 'asc'),
            'area_desc'  => $query->orderBy('area', 'desc'),
            default      => $query->latest(),
        };

        $spaces = $query->paginate(12)->withQueryString();
        $bookmarkedSpaceIds = Auth::check() ? Auth::user()->bookmarkedSpaces()->pluck('space_id')->toArray() : [];

        return view('public.dashboard', compact('spaces', 'bookmarkedSpaceIds'));
    }

    public function show(Space $space){
        if ($space->status->code !== 'spc_available') {
            abort(404, 'This space is currently unavailable.');
        }

        $space->load(['location', 'registration.prices.pricingType', 'photos', 'registration.photos', 'owner']);

        $isBookmarked = Auth::check() && Auth::user()->bookmarkedSpaces()->where('space_id', $space->id)->exists();

        return view('public.spaces.show', compact('space', 'isBookmarked'));
    }
}