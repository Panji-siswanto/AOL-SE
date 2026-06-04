<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller {

    public function index() {
        $user = Auth::user();
        
        $spaces = $user->bookmarkedSpaces()
            ->with(['location', 'photos', 'registration.photos', 'registration.prices'])
            ->latest('bookmarks.created_at')
            ->paginate(12);

        $bookmarkedSpaceIds = $spaces->pluck('id')->toArray();

        return view('public.bookmarks.index', compact('spaces', 'bookmarkedSpaceIds'));
    }

    public function toggle(Space $space) {
        $user = Auth::user();
        
        $user->bookmarkedSpaces()->toggle($space->id);
        $isBookmarked = $user->bookmarkedSpaces()->where('space_id', $space->id)->exists();
        
        return response()->json(['bookmarked' => $isBookmarked]);
    }
}