<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpaceRegistration;
use App\Models\Status;
use App\Models\User;
use App\Models\VerificationLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Identity verifications waiting for approval
        $pendingVerifications = VerificationLog::where('status_id', Status::USR_VERIFY_PENDING)->count();

        // 2. New space submissions waiting for admin moderation
        $pendingListings = SpaceRegistration::where('status_id', Status::REG_PENDING)->count();

        // 3. Total registered accounts
        $totalUsers = User::count();
        $Admin = Auth::user();

        return view('admin.index', compact('pendingVerifications', 'pendingListings', 'totalUsers','Admin'));
    }
}