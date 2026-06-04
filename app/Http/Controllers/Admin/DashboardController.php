<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpaceRegistration;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(){
        $admin = Auth::user();

        $regPending = Status::where('code', 'reg_pending')->value('id');
        $usrPending = Status::where('code', 'usr_verify_pending')->value('id');

        $pendingListings = SpaceRegistration::where('status_id', $regPending)->count();
        $pendingVerifications = User::where('ver_status', $usrPending)->count();

        return view('admin.index', compact('admin', 'pendingListings', 'pendingVerifications'));
    }
}