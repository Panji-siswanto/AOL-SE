<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    // Renders the page showing all saved spaces
    public function index()
    {
        $user = Auth::user();
        
        // Fetch bookmarked spaces, eager loading the same data we need for the cards
        $spaces = $user->bookmarkedSpaces()
            ->with(['location', 'photos', 'registration.photos', 'registration.prices'])
            ->latest('bookmarks.created_at') // Sort by most recently saved
            ->paginate(12);

        // We still need this array so the toggle buttons on the page know they are active!
        $bookmarkedSpaceIds = $spaces->pluck('id')->toArray();

        return view('public.bookmarks.index', compact('spaces', 'bookmarkedSpaceIds'));
    }

    // Handles the Alpine.js AJAX toggle action
    public function toggle(Space $space)
    {
        $user = Auth::user();
        
        $user->bookmarkedSpaces()->toggle($space->id);
        
        $isBookmarked = $user->bookmarkedSpaces()->where('space_id', $space->id)->exists();
        
        return response()->json(['bookmarked' => $isBookmarked]);
    }
}