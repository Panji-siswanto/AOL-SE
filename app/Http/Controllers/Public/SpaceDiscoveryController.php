<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceDiscoveryController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Base Query
        $query = Space::with(['location', 'photos', 'registration.photos', 'registration.prices'])
            ->whereHas('status', function ($q) {
                $q->where('code', 'spc_available');
            });

        // Apply Search Filter (Name, City, Address)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('location', function ($qLoc) use ($search) {
                      $qLoc->where('city', 'like', "%{$search}%")
                           ->orWhere('address', 'like', "%{$search}%");
                  });
            });
        }

        // Apply Price Filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply Area Filters
        if ($request->filled('min_area')) {
            $query->where('area', '>=', $request->min_area);
        }
        if ($request->filled('max_area')) {
            $query->where('area', '<=', $request->max_area);
        }

        // Apply Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'area_desc':
                $query->orderBy('area', 'desc');
                break;
            case 'area_asc': 
                $query->orderBy('area', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // Fetch Paginated Results
        $spaces = $query->paginate(6)->withQueryString();
        
        $bookmarkedSpaceIds = [];
        if (Auth::check()) {
            $bookmarkedSpaceIds = Auth::user()->bookmarkedSpaces()->pluck('space_id')->toArray();
        }

        return view('public.dashboard', compact('spaces', 'bookmarkedSpaceIds'));
    }

    public function show(Space $space)
    {
        // Prevent viewing if the space is paused or unlisted
        if ($space->status->code !== 'spc_available') {
            abort(404, 'This space is currently unavailable.');
        }

        // Load relationships
        $space->load([
            'location', 
            'registration.prices.pricingType', 
            'photos', 
            'registration.photos',
            'owner'
        ]);

        // Return the public-facing view
        $isBookmarked = Auth::check() && Auth::user()->bookmarkedSpaces()->where('space_id', $space->id)->exists();

        return view('public.spaces.show', compact('space', 'isBookmarked'));    
    }
}