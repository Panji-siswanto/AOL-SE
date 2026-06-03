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
        $pendingStatusId = Status::where('code', 'reg_pending')->value('id');

        $query = SpaceRegistration::with(['location', 'status', 'owner', 'documents.documentType', 'prices.pricingType'])
            ->where('status_id', $pendingStatusId)
            ->search($request->search);

        if ($request->sort_date === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $requests = $query->get();
        $pendingCount = $requests->count();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $requests]);
        }

        return view('admin.listing-request.index', compact('requests', 'pendingCount'));
    }

    public function history(Request $request)
    {
        $approvedStatusId = Status::where('code', 'reg_approved')->value('id');
        $rejectedStatusId = Status::where('code', 'reg_rejected')->value('id');

        $query = SpaceRegistration::with(['location', 'status', 'owner', 'documents.documentType', 'prices.pricingType', 'logs.admin'])
            ->whereIn('status_id', [$approvedStatusId, $rejectedStatusId])
            ->search($request->search)        
            ->withStatus($request->status);   

        if ($request->sort_date === 'oldest') {
            $query->oldest('updated_at');
        } else {
            $query->latest('updated_at');
        }

        $historicalRequests = $query->paginate(15)->withQueryString();

        return view('admin.listing-request.history', compact('historicalRequests'));
    }
  
    public function approve(Request $request, SpaceRegistration $registration)
    {
        $owner = $registration->owner;
        $verifiedStatusId = Status::where('code', 'usr_verified')->value('id');

        if ($owner->ver_status !== $verifiedStatusId) {
            return redirect()->back()->with('error', 'Cannot approve listing. The host\'s identity (KTP) must be verified first.');
        }

        DB::beginTransaction();
        try {
            $approvedStatusId = Status::where('code', 'reg_approved')->value('id');
            $registration->update(['status_id' => $approvedStatusId]);

            $basePrice = $registration->prices()->min('price') ?? 0;

            // FIX: Map the new dimension columns instead of the old 'size' string
            $space = Space::create([
                'owner_id' => $registration->owner_id,
                'location_id' => $registration->location_id,
                'registration_id' => $registration->id,
                'name' => $registration->name,
                'description' => $registration->description,
                'length' => $registration->length,
                'width' => $registration->width,
                'area' => $registration->area,
                'price' => $basePrice, 
                'status_id' => Status::where('code', 'spc_available')->value('id'),
            ]);

            $user = User::findOrFail($registration->owner_id);
            if (!$user->hasRole('owner')) {
                $user->assignRole('owner');
            }

            RegistrationLog::create([
                'registration_id' => $registration->id,
                'admin_id' => Auth::id(),
                'note' => $request->note ?? 'Space listing formally approved and published to catalog.',
            ]);

            DB::commit();

            return redirect()->back()->with('success', "Space listing '{$space->name}' has been approved and published.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, SpaceRegistration $registration)
    {
        $request->validate(['note' => 'required|string|max:500']);
        
        DB::beginTransaction();
        try {
            $rejectedStatusId = Status::where('code', 'reg_rejected')->value('id');
            $registration->update(['status_id' => $rejectedStatusId]);
            
            RegistrationLog::create([
                'registration_id' => $registration->id,
                'admin_id' => Auth::id(),
                'note' => $request->note, 
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Space listing application has been rejected.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }
}