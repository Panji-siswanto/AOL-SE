<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpaceRegistration;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Fetch the authenticated Admin user
        $Admin = Auth::user();

        // 2. Count Pending Space Listings
        $pendingRegStatusId = Status::where('code', 'reg_pending')->value('id');
        $pendingListings = SpaceRegistration::where('status_id', $pendingRegStatusId)->count();

        // 3. Count Pending User Verifications
        $pendingVerStatusId = Status::where('code', 'usr_verify_pending')->value('id');
        $pendingVerifications = User::where('ver_status', $pendingVerStatusId)->count();

        // Pass exactly what the Blade template expects
        // Note: Change 'admin.index' to 'admin.dashboard' if your file is named dashboard.blade.php
        return view('admin.index', compact('Admin', 'pendingListings', 'pendingVerifications'));
    }
}