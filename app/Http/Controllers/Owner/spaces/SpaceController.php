<?php

namespace App\Http\Controllers\Owner\spaces;

use App\Http\Controllers\Controller;
use App\Models\PricingType;
use App\Models\Space;
use App\Models\SpacePhoto;
use App\Models\SpaceRegistration;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SpaceController extends Controller 
{
    public function index(Request $request) 
    {
        $activeTab = $request->input('tab', 'live');
        $ownerId = Auth::id();

        $regQuery = SpaceRegistration::with(['location', 'status'])->where('owner_id', $ownerId);
        if ($activeTab === 'applications') {
            $regQuery->search($request->search)->withStatus($request->status);
            $request->sort_date === 'oldest' ? $regQuery->oldest() : $regQuery->latest();
        } else {
            $regQuery->latest();
        }
        $registrations = $regQuery->get();

        $spcQuery = Space::with(['location', 'status'])->where('owner_id', $ownerId);
        if ($activeTab === 'live') {
            if ($request->filled('search')) $spcQuery->where('name', 'like', "%{$request->search}%");
            if ($request->filled('status')) {
                $spcQuery->whereHas('status', fn($q) => $q->where('code', $request->status));
            }
            $request->sort_date === 'oldest' ? $spcQuery->oldest() : $spcQuery->latest();
        } else {
            $spcQuery->latest();
        }
        $spaces = $spcQuery->get();

        return view('owner.spaces.index', compact('registrations', 'spaces', 'activeTab'));
    }

    public function show(Space $space) 
    {
        $this->authorizeOwner($space);
        $space->load(['location', 'status', 'registration.prices.pricingType', 'photos', 'registration.photos']);
        return view('owner.spaces.show', compact('space'));
    }

    public function edit(Space $space) 
    {
        $this->authorizeOwner($space);
        
        if ($space->status->code === 'spc_unlisted') {
            return redirect()->route('owner.spaces.show', $space->id)->with('error', 'Unlisted spaces cannot be edited.');
        }

        $pricingTypes = PricingType::all();
        $space->load(['location', 'registration.prices', 'photos', 'registration.photos']);
        return view('owner.spaces.edit', compact('space', 'pricingTypes'));
    }

    public function update(Request $request, Space $space) 
    {
        $this->authorizeOwner($space);

        if ($space->status->code === 'spc_unlisted') {
            return redirect()->route('owner.spaces.show', $space->id)->with('error', 'Unlisted spaces cannot be edited.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'dimension_type' => 'required|in:exact,total',
            'length' => 'nullable|numeric|required_if:dimension_type,exact',
            'width' => 'nullable|numeric|required_if:dimension_type,exact',
            'area' => 'nullable|numeric|required_if:dimension_type,total',
            'pricing' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $this->updateLocation($space, $request);
            $this->updateSpaceData($space, $request);
            $this->syncPricing($space, $request->pricing);
            $this->manageGallery($space, $request);

            DB::commit();
            return redirect()->route('owner.spaces.show', $space->id)->with('success', 'Space updated!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Update Failed: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Space $space) 
    {
        $this->authorizeOwner($space);
        $request->validate(['action' => 'required|in:pause,unpause,unlist']);

        if ($space->status->code === 'spc_unlisted') {
            return redirect()->route('owner.spaces.show', $space->id)->with('error', 'This space is permanently unlisted and cannot be changed.');
        }

        $codes = ['pause' => 'spc_paused', 'unpause' => 'spc_available', 'unlist' => 'spc_unlisted'];
        $space->update(['status_id' => Status::where('code', $codes[$request->action])->value('id')]);

        return back()->with('success', 'Status updated successfully.');
    }

    private function authorizeOwner(Space $space) 
    {
        if ($space->owner_id !== Auth::id()) abort(403);
    }

    private function updateLocation(Space $space, Request $request) 
    {
        $space->location->update($request->only(['city', 'province', 'address', 'latitude', 'longitude']));
    }

    private function updateSpaceData(Space $space, Request $request) 
    {
        $area = $request->dimension_type === 'exact' ? ($request->length * $request->width) : $request->area;
        $data = [
            'name' => $request->name, 
            'description' => $request->description,
            'length' => $request->dimension_type === 'exact' ? $request->length : null, 
            'width' => $request->dimension_type === 'exact' ? $request->width : null, 
            'area' => $area,
        ];
        $space->update($data);
        $space->registration->update($data);
    }

    private function syncPricing(Space $space, array $pricing) 
    {
        $space->registration->prices()->delete();
        $basePrice = null;

        foreach ($pricing as $typeId => $data) {
            if (isset($data['is_active']) && !empty($data['price'])) {
                $space->registration->prices()->create(['pricing_type_id' => $typeId, 'price' => $data['price']]);
                if (is_null($basePrice) || $data['price'] < $basePrice) $basePrice = $data['price'];
            }
        }
        if ($basePrice) $space->update(['price' => $basePrice]);
    }

    private function manageGallery(Space $space, Request $request) 
    {
        if ($request->filled('deleted_photos')) {
            $ids = array_filter(explode(',', $request->deleted_photos));
            if (!empty($ids)) {
                SpacePhoto::whereIn('id', $ids)->where('space_id', $space->id)->each(function($p) {
                    if ($p->file_path) Storage::disk('public')->delete($p->file_path);
                    $p->delete();
                });
            }
        }

        if ($request->hasFile('new_photos')) {
            foreach ($request->file('new_photos') as $file) {
                SpacePhoto::create([
                    'space_registration_id' => $space->registration_id,
                    'space_id' => $space->id,
                    'file_path' => $file->store("spaces/gallery/reg_{$space->registration_id}", 'public')
                ]);
            }
        }

        $primaryId = $request->primary_photo_id;
        SpacePhoto::where('space_id', $space->id)->update(['is_primary' => false]);
        
        if ($primaryId && $primaryId !== 'null') {
            SpacePhoto::where('space_id', $space->id)->where('id', $primaryId)->update(['is_primary' => true]);
        } else {
            $fallback = SpacePhoto::where('space_id', $space->id)->first();
            if ($fallback) $fallback->update(['is_primary' => true]);
        }
    }
}