<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaceRegistrationRequest;
use App\Models\DocumentType;
use App\Models\Location;
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
        // Fixed missing function parentheses on auth()->id()
        $query = SpaceRegistration::with(['location', 'status'])
            ->where('owner_id', Auth::id()); 

        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('code', $request->status);
            });
        }

        $sortOrder = $request->input('sort', 'description'); 
        $query->orderBy('created_at', $sortOrder);

        $registrations = $query->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $registrations
            ]);
        }

        return view('space-registration.index', compact('registrations'));
    }

    public function create()
    {
        return view('space-registration.create');
    }

    public function show(Request $request, $id)
    {
        // Fixed missing function parentheses on auth()->id()
        // Ensure your SpaceDocument model maps the parent relation via documentType()
        $registration = SpaceRegistration::with(['location', 'status', 'documents.documentType', 'photos'])
            ->where('owner_id', Auth::id()) 
            ->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $registration
            ]);
        }

        // return view('space-registration.show', compact('registration'));
    }

    public function store(StoreSpaceRegistrationRequest $request)
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // =========================================================================
            // 1. IDENTITY VERIFICATION INTEGRATION (NORMALIZED STAGING)
            // =========================================================================
            // If the user has not verified their identity, route KTP payloads to staging logs
            if ($user->ver_status == Status::USR_UNVERIFIED || $user->ver_status == Status::USR_REJECTED) {
                
                if ($request->hasFile('ktp') && $request->hasFile('selfie_ktp')) {
                    $ktpPath = $request->file('ktp')->store("staging/verifications/{$user->id}", 'public');
                    $selfiePath = $request->file('selfie_ktp')->store("staging/verifications/{$user->id}", 'public');

                    // Resolve document type classifications dynamically by string code
                    $ktpTypeId = DocumentType::where('code', 'ktp')->value('id');
                    $selfieTypeId = DocumentType::where('code', 'selfie_ktp')->value('id');

                    // Instantiate the staging log natively without flat path parameters
                    $log = VerificationLog::create([
                        'user_id' => $user->id,
                        'status_id' => Status::USR_VERIFY_PENDING,
                    ]);

                    // Clone file records directly into the normalized verification_documents schema
                    $log->documents()->createMany([
                        [
                            'document_type_id' => $ktpTypeId,
                            'file_path' => $ktpPath,
                            'description' => 'Identity Card (KTP) Submission during Space Registration'
                        ],
                        [
                            'document_type_id' => $selfieTypeId,
                            'file_path' => $selfiePath,
                            'description' => 'Selfie Verification Submission during Space Registration'
                        ],
                    ]);

                    // Elevate parent user context state
                    $user->update(['ver_status' => Status::USR_VERIFY_PENDING]);
                }
            }

            // =========================================================================
            // 2. PERSIST LOCATION COORDINATES
            // =========================================================================
            $location = Location::create([
                'city' => $request->city,
                'province' => $request->province,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // =========================================================================
            // 3. GENERATE SPACE REGISTRATION APPLICATION
            // =========================================================================
            $registration = SpaceRegistration::create([
                'owner_id' => $user->id, 
                'location_id' => $location->id,
                'name' => $request->name,
                'description' => $request->description,
                'size' => $request->size,
                'price' => $request->price,
                'status_id' => Status::REG_PENDING,
            ]);

            // =========================================================================
            // 4. MAP LEGAL SPACE ASSETS (ERD-ALIGNED COLUMNS)
            // =========================================================================
            // Dynamically resolve core document type mappings
            $suratTanahTypeId = DocumentType::where('code', 'surat_tanah')->value('id');
            $suratIzinTypeId = DocumentType::where('code', 'perjanjian_sewa')->value('id') ?? DocumentType::where('code', 'surat_izin')->value('id');

            // Save primary document (Surat Tanah / Sertifikat Lahan) using strict ERD keys
            if ($request->hasFile('surat_tanah')) {
                $tanahPath = $request->file('surat_tanah')->store("documents/spaces/reg_{$registration->id}", 'public');
                SpaceDocument::create([
                    'registration_id' => $registration->id, // Mapped to registration_id per ERD
                    'document_type_id' => $suratTanahTypeId,        // Mapped to document_type_id per ERD
                    'file_path' => $tanahPath,
                    'description' => 'Sertifikat Hak Milik / Surat Izin Lahan', // Mapped to desc column
                ]);
            }

            // Save optional secondary space agreement document
            if ($request->hasFile('surat_izin')) {
                $izinPath = $request->file('surat_izin')->store("documents/spaces/reg_{$registration->id}", 'public');
                SpaceDocument::create([
                    'registration_id' => $registration->id,
                    'document_type_id' => $suratIzinTypeId,
                    'file_path' => $izinPath,
                    'description' => 'Surat Izin / Perjanjian Sewa Pendukung',
                ]);
            }

            // =========================================================================
            // 5. PERSIST GALLERY IMAGES (ERD-ALIGNED COLUMNS)
            // =========================================================================
            if ($request->hasFile('photos')) {
                $descriptions = $request->input('photo_descriptions', []);

                foreach ($request->file('photos') as $index => $photo) {
                    $photoPath = $photo->store("spaces/gallery/reg_{$registration->id}", 'public');
                    
                    SpacePhoto::create([
                        'registration_id' => $registration->id, // Mapped to registration_id per ERD
                        'space_id' => null, // Remains null until Admin authorization promotes it to active marketplace catalogs
                        'file_path' => $photoPath,
                        'description' => $descriptions[$index] ?? null, // Mapped to desc column per ERD
                        'is_primary' => $index === 0, // Initial photo buffer defaults as cover thumbnail
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Space registration submitted successfully. Application is pending review.',
                    'data' => $registration->load(['location', 'documents.documentType', 'photos'])
                ], 201);
            }

            return redirect()->route('space-registrations.index')
                ->with('success', 'Space listing application submitted successfully! Please wait for admin approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to submit space registration.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Processing failed: ' . $e->getMessage());
        }
    }
}