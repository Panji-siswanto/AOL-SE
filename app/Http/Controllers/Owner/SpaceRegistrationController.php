<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaceRegistrationRequest;
use App\Models\{DocumentType, Location, PricingType, SpaceDocument, SpacePhoto, SpaceRegistration, Status, VerificationLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpaceRegistrationController extends Controller
{
    public function create()
    {
        return view('owner.spaces.space-registration.create', ['pricingTypes' => PricingType::all()]);
    }

    public function show(Request $request, $id)
    {
        $registration = SpaceRegistration::with(['location', 'status', 'documents.documentType', 'photos', 'prices.pricingType', 'logs'])
            ->where('owner_id', Auth::id())
            ->findOrFail($id);

        return $request->wantsJson()
            ? response()->json(['status' => 'success', 'data' => $registration])
            : view('owner.spaces.space-registration.show', compact('registration'));
    }

    public function store(StoreSpaceRegistrationRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $this->handleUserVerificationIfNecessary($request, $user);

            $location = $this->createLocation($request);
            $registration = $this->createRegistration($request, $user, $location);
            
            $this->savePricing($request, $registration);
            $this->saveDocuments($request, $registration);
            $this->savePhotos($request, $registration);

            DB::commit();

            return $request->wantsJson()
                ? response()->json(['status' => 'success', 'data' => $registration], 201)
                : redirect()->route('owner.spaces.index', ['tab' => 'applications'])
                    ->with('success', 'Application submitted! Moderation team will review it shortly.');

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

        DB::transaction(function () use ($registration, $photoIds) {
            SpacePhoto::where('space_registration_id', $registration->id)->update(['is_primary' => false]);
            SpacePhoto::where('id', $photoIds[0])
                ->where('space_registration_id', $registration->id)
                ->update(['is_primary' => true]);
        });

        return response()->json(['status' => 'success']);
    }

    // Helpers
    private function handleUserVerificationIfNecessary($request, $user)
    {
        $pendingStatus = Status::where('code', 'usr_verify_pending')->value('id');
        $unverifiedStatuses = [
            Status::where('code', 'usr_unverified')->value('id'),
            Status::where('code', 'usr_rejected')->value('id')
        ];

        if (in_array($user->ver_status, $unverifiedStatuses) && $request->hasFile('ktp')) {
            $ktpPath = $request->file('ktp')->store("staging/verifications/{$user->id}", 'public');
            $selfiePath = $request->file('selfie_ktp')->store("staging/verifications/{$user->id}", 'public');

            $log = VerificationLog::create(['user_id' => $user->id, 'status_id' => $pendingStatus]);
            $log->documents()->createMany([
                ['document_type_id' => DocumentType::where('code', 'ktp')->value('id'), 'file_path' => $ktpPath, 'description' => 'KTP Submission'],
                ['document_type_id' => DocumentType::where('code', 'selfie_ktp')->value('id'), 'file_path' => $selfiePath, 'description' => 'Selfie Submission'],
            ]);
            $user->update(['ver_status' => $pendingStatus]);
        }
    }

    private function createLocation($request)
    {
        return Location::create($request->only(['city', 'province', 'address', 'latitude', 'longitude']));
    }

    private function createRegistration($request, $user, $location)
    {
        $area = $request->dimension_type === 'exact' ? ($request->length * $request->width) : $request->area;
        return SpaceRegistration::create([
            'owner_id' => $user->id,
            'location_id' => $location->id,
            'name'=> $request->name,
            'description'=> $request->description,
            'length' => $request->dimension_type === 'exact' ? $request->length : null,
            'width'=> $request->dimension_type === 'exact' ? $request->width : null,
            'area' => $area,
            'status_id '=> Status::where('code', 'reg_pending')->value('id'),
        ]);
    }

    private function savePricing($request, $registration)
    {
        if ($request->has('pricing')) {
            foreach ($request->pricing as $typeId => $data) {
                if (($data['is_active'] ?? 0) == '1' && !empty($data['price'])) {
                    $registration->prices()->create(['pricing_type_id' => $typeId, 'price' => $data['price']]);
                }
            }
        }
    }

    private function saveDocuments($request, $registration)
    {
        $docs = ['surat_tanah' => 'surat_tanah', 'surat_izin' => 'perjanjian_sewa'];
        foreach ($docs as $inputName => $code) {
            if ($request->hasFile($inputName)) {
                SpaceDocument::create([
                    'space_registration_id' => $registration->id,
                    'document_type_id' => DocumentType::where('code', $code)->value('id'),
                    'file_path' => $request->file($inputName)->store("spaces/docs/reg_{$registration->id}", 'public'),
                    'description' => $inputName === 'surat_tanah' ? 'SHM' : 'Surat Perjanjian Sewa',
                ]);
            }
        }
    }

    private function savePhotos($request, $registration)
    {
        if ($request->hasFile('photos')) {
            $primary = (int) $request->input('primary_photo_index', 0);
            foreach ($request->file('photos') as $i => $photo) {
                SpacePhoto::create([
                    'space_registration_id' => $registration->id,
                    'file_path' => $photo->store("spaces/gallery/reg_{$registration->id}", 'public'),
                    'is_primary' => $i === $primary,
                ]);
            }
        }
    }
}