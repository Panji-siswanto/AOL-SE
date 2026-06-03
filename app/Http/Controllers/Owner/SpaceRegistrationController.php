<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaceRegistrationRequest;
use App\Models\DocumentType;
use App\Models\Location;
use App\Models\PricingType;
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
    public function create()
    {
        $pricingTypes = PricingType::all();        
        return view('owner.spaces.space-registration.create', compact('pricingTypes'));
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

        return view('owner.spaces.space-registration.show', compact('registration'));
    }

    public function store(StoreSpaceRegistrationRequest $request)
    {
        $user = Auth::user();
        DB::beginTransaction();

        try {
            $unverifiedStatusId = Status::where('code', 'usr_unverified')->value('id');
            $rejectedStatusId = Status::where('code', 'usr_rejected')->value('id');

            if ($user->ver_status == $unverifiedStatusId || $user->ver_status == $rejectedStatusId) {
                if ($request->hasFile('ktp') && $request->hasFile('selfie_ktp')) {
                    $ktpPath = $request->file('ktp')->store("staging/verifications/{$user->id}", 'public');
                    $selfiePath = $request->file('selfie_ktp')->store("staging/verifications/{$user->id}", 'public');

                    $ktpTypeId = DocumentType::where('code', 'ktp')->value('id');
                    $selfieTypeId = DocumentType::where('code', 'selfie_ktp')->value('id');
                    $pendingUserStatusId = Status::where('code', 'usr_verify_pending')->value('id');

                    $log = VerificationLog::create([
                        'user_id' => $user->id, 
                        'status_id' => $pendingUserStatusId, 
                    ]);

                    $log->documents()->createMany([
                        ['document_type_id' => $ktpTypeId, 'file_path' => $ktpPath, 'description' => 'KTP Submission'],
                        ['document_type_id' => $selfieTypeId, 'file_path' => $selfiePath, 'description' => 'Selfie Verification Submission'],
                    ]);

                    $user->update(['ver_status' => $pendingUserStatusId]);
                }
            }

            $location = Location::create([
                'city' => $request->city,
                'province' => $request->province,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Area Calculation
            $calculatedArea = $request->dimension_type === 'exact' 
                ? ($request->length * $request->width) 
                : $request->area;

            $registrationStatusId = Status::where('code', 'reg_pending')->value('id');
            
            $registration = SpaceRegistration::create([
                'owner_id' => $user->id, 
                'location_id' => $location->id, 
                'name' => $request->name,
                'description' => $request->description,
                'length' => $request->dimension_type === 'exact' ? $request->length : null,
                'width' => $request->dimension_type === 'exact' ? $request->width : null,
                'area' => $calculatedArea,
                'status_id' => $registrationStatusId, 
            ]);

            if ($request->has('pricing')) {
                foreach ($request->pricing as $typeId => $pricingData) {
                    if (isset($pricingData['is_active']) && $pricingData['is_active'] == '1' && !empty($pricingData['price'])) {
                        $registration->prices()->create([
                            'pricing_type_id' => $typeId,
                            'price' => $pricingData['price'],
                        ]);
                    }
                }
            }

            $suratTanahTypeId = DocumentType::where('code', 'surat_tanah')->value('id');
            $perjanjianSewaTypeId = DocumentType::where('code', 'perjanjian_sewa')->value('id');

            if ($request->hasFile('surat_tanah')) {
                $tanahPath = $request->file('surat_tanah')->store("/spaces/documents/reg_{$registration->id}", 'public');
                SpaceDocument::create([
                    'space_registration_id' => $registration->id, 
                    'document_type_id' => $suratTanahTypeId,
                    'file_path' => $tanahPath,
                    'description' => 'Sertifikat Hak Milik',
                ]);
            }

            if ($request->hasFile('surat_izin')) {
                $izinPath = $request->file('surat_izin')->store("documents/spaces/reg_{$registration->id}", 'public');
                SpaceDocument::create([
                    'space_registration_id' => $registration->id, 
                    'document_type_id' => $perjanjianSewaTypeId, 
                    'file_path' => $izinPath,
                    'description' => 'Surat Perjanjian Sewa Induk',
                ]);
            }

            if ($request->hasFile('photos')) {
                $descriptions = $request->input('photo_descriptions', []);
                $primaryIndex = (int) $request->input('primary_photo_index', 0);

                foreach ($request->file('photos') as $index => $photo) {
                    $photoPath = $photo->store("spaces/gallery/reg_{$registration->id}", 'public');
                    
                    SpacePhoto::create([
                        'space_registration_id' => $registration->id,
                        'space_id' => null,
                        'file_path' => $photoPath,
                        'description' => $descriptions[$index] ?? null,
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
            
            // CHANGED: Redirects to the Dashboard on the Applications tab
            return redirect()->route('owner.spaces.index', ['tab' => 'applications'])
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

        $registration = SpaceRegistration::where('owner_id', Auth::id())->findOrFail($id);
        $photoIds = $request->photo_ids;

        if (count($photoIds) > 0) {
            SpacePhoto::where('space_registration_id', $registration->id)->update(['is_primary' => false]);
            SpacePhoto::where('id', $photoIds[0])->update(['is_primary' => true]);
        }

        return response()->json(['status' => 'success']);
    }
}