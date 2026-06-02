<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaceRegistrationRequest;
use App\Models\DocumentType;
use App\Models\Location;
use App\Models\PricingType;
use App\Models\Space;
use App\Models\SpaceDocument;
use App\Models\SpacePhoto;
use App\Models\SpaceRegistration;
use App\Models\Status;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpaceRegistrationController extends Controller
{
  public function index(Request $request)
    {
        // Determine which tab is active (defaults to 'live')
        $activeTab = $request->input('tab', 'live');

        // 1. Fetch Registrations (Applications)
        $registrationsQuery = SpaceRegistration::with(['location', 'status'])
            ->where('owner_id', Auth::id());

        // Only apply filters to registrations if we are searching on the applications tab
        if ($activeTab === 'applications') {
            $registrationsQuery->search($request->search)->withStatus($request->status);
            if ($request->sort_date === 'oldest') {
                $registrationsQuery->oldest();
            } else {
                $registrationsQuery->latest();
            }
        } else {
            $registrationsQuery->latest(); // Default sort
        }
        
        $registrations = $registrationsQuery->get();

        // 2. Fetch Live Spaces
        $spacesQuery = Space::with(['location', 'status'])
            ->where('owner_id', Auth::id());
            
        // Only apply filters to spaces if we are searching on the live tab
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
            $spacesQuery->latest(); // Default sort
        }

        $spaces = $spacesQuery->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'registrations' => $registrations,
                'spaces' => $spaces
            ]);
        }

        return view('space-registration.index', compact('registrations', 'spaces', 'activeTab'));
    }
    public function show(Request $request, $id)
    {
        $registration = SpaceRegistration::with(['location', 'status', 'documents.documentType', 'photos', 'prices.pricingType', 'logs'])
            ->where('owner_id', Auth::id()) 
            ->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $registration
            ]);
        }

        return view('space-registration.show', compact('registration'));
    }
    public function create()
    {
        $pricingTypes = PricingType::all();        
        return view('space-registration.create', compact('pricingTypes'));
    }


   public function store(StoreSpaceRegistrationRequest $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // 1. IDENTITY VERIFICATION
            // Using the exact lowercase codes from your StatusSeeder
            $unverifiedStatusId = Status::where('code', 'usr_unverified')->value('id');
            $rejectedStatusId = Status::where('code', 'usr_rejected')->value('id');

            if ($user->ver_status == $unverifiedStatusId || $user->ver_status == $rejectedStatusId) {
                
                if ($request->hasFile('ktp') && $request->hasFile('selfie_ktp')) {
                    $ktpPath = $request->file('ktp')->store("staging/verifications/{$user->id}", 'public');
                    $selfiePath = $request->file('selfie_ktp')->store("staging/verifications/{$user->id}", 'public');

                    // Using exact codes from DocumentTypeSeeder
                    $ktpTypeId = DocumentType::where('code', 'ktp')->value('id');
                    $selfieTypeId = DocumentType::where('code', 'selfie_ktp')->value('id');
                    $pendingUserStatusId = Status::where('code', 'usr_verify_pending')->value('id');

                    $log = VerificationLog::create([
                        'user_id' => $user->id, 
                        'status_id' => $pendingUserStatusId, 
                    ]);

                    // Note: Ensure your VerificationDocument model/migration matches these columns
                    $log->documents()->createMany([
                        [
                            'document_type_id' => $ktpTypeId, 
                            'file_path' => $ktpPath,
                            'description' => 'KTP Submission' 
                        ],
                        [
                            'document_type_id' => $selfieTypeId,
                            'file_path' => $selfiePath,
                            'description' => 'Selfie Verification Submission'
                        ],
                    ]);

                    $user->update(['ver_status' => $pendingUserStatusId]);
                }
            }

            // 2. PERSIST LOCATION (Matches your locations migration exactly)
            $location = Location::create([
                'city' => $request->city,
                'province' => $request->province,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // 3. GENERATE SPACE REGISTRATION (Matches space_registrations migration exactly)
            $registrationStatusId = Status::where('code', 'reg_pending')->value('id');
            
            $registration = SpaceRegistration::create([
                'owner_id' => $user->id, 
                'location_id' => $location->id, 
                'name' => $request->name,
                'description' => $request->description,
                'size' => $request->size,
                'status_id' => $registrationStatusId, 
            ]);

            // 4. PROCESS DYNAMIC PRICING ARRAY (Matches space_registration_prices migration)
            if ($request->has('pricing')) {
                foreach ($request->pricing as $typeId => $pricingData) {
                    if (isset($pricingData['is_active']) && $pricingData['is_active'] == '1' && !empty($pricingData['price'])) {
                        $registration->prices()->create([
                            'pricing_type_id' => $typeId, // Exact match to migration
                            'price' => $pricingData['price'],
                        ]);
                    }
                }
            }

            // 5. MAP LEGAL SPACE ASSETS
            // Using exact codes from DocumentTypeSeeder
            $suratTanahTypeId = DocumentType::where('code', 'surat_tanah')->value('id');
            $perjanjianSewaTypeId = DocumentType::where('code', 'perjanjian_sewa')->value('id');

            if ($request->hasFile('surat_tanah')) {
                $tanahPath = $request->file('surat_tanah')->store("documents/spaces/reg_{$registration->id}", 'public');
                SpaceDocument::create([
                    'space_registration_id' => $registration->id, 
                    'document_type_id' => $suratTanahTypeId,
                    'file_path' => $tanahPath,
                    'description' => 'Sertifikat Hak Milik',
                ]);
            }

            // The input field is still named 'surat_izin' on frontend
            if ($request->hasFile('surat_izin')) {
                $izinPath = $request->file('surat_izin')->store("documents/spaces/reg_{$registration->id}", 'public');
                SpaceDocument::create([
                    'space_registration_id' => $registration->id, 
                    'document_type_id' => $perjanjianSewaTypeId, // Mapped to perjanjian_sewa
                    'file_path' => $izinPath,
                    'description' => 'Surat Perjanjian Sewa Induk',
                ]);
            }

           // 6. PERSIST GALLERY IMAGES
            if ($request->hasFile('photos')) {
                $descriptions = $request->input('photo_descriptions', []);
                
                // Get the user-selected cover index from the input payload
                $primaryIndex = (int) $request->input('primary_photo_index', 0);

                foreach ($request->file('photos') as $index => $photo) {
                    $photoPath = $photo->store("spaces/gallery/reg_{$registration->id}", 'public');
                    
                    SpacePhoto::create([
                        'space_registration_id' => $registration->id,
                        'space_id' => null,
                        'file_path' => $photoPath,
                        'description' => $descriptions[$index] ?? null,
                        // Check if the current loop iteration matches the user's selected cover index
                        'is_primary' => $index === $primaryIndex, 
                    ]);
                }
            }

       DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'data' => $registration->load(['location', 'documents', 'photos', 'prices'])
                ], 201);
            }
            return redirect()->route('space-registrations.create')
                ->with('success', 'Space listing application submitted successfully! Our moderation team will review it shortly.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Database Error: ' . $e->getMessage());
        }
    }

    public function reorderPhotos(Request $request, $id)
    {
        $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'exists:space_photos,id'
        ]);

        // Security check: Ensure the registration belongs to the auth user
        $registration = SpaceRegistration::where('owner_id', Auth::id())->findOrFail($id);

        $photoIds = $request->photo_ids;

        if (count($photoIds) > 0) {
            // Reset all photos for this registration to NOT primary
            SpacePhoto::where('space_registration_id', $registration->id)
                ->update(['is_primary' => false]);

            // Set the first photo in the newly sorted array to primary
            SpacePhoto::where('id', $photoIds[0])
                ->update(['is_primary' => true]);
        }

        return response()->json(['status' => 'success']);
    }
}