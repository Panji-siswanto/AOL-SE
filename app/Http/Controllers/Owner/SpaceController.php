<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PricingType;
use App\Models\Space;
use App\Models\SpacePhoto;
use App\Models\SpaceRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SpaceController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'live');

        $registrationsQuery = SpaceRegistration::with(['location', 'status'])
            ->where('owner_id', Auth::id());

        if ($activeTab === 'applications') {
            $registrationsQuery->search($request->search)->withStatus($request->status);
            $request->sort_date === 'oldest' ? $registrationsQuery->oldest() : $registrationsQuery->latest();
        } else {
            $registrationsQuery->latest();
        }
        $registrations = $registrationsQuery->get();

        $spacesQuery = Space::with(['location', 'status'])
            ->where('owner_id', Auth::id());
            
        if ($activeTab === 'live') {
            if ($request->filled('search')) {
                $spacesQuery->where('name', 'like', "%{$request->search}%");
            }
            if ($request->filled('status')) {
                $spacesQuery->whereHas('status', function ($q) use ($request) {
                    $q->where('code', $request->status);
                });
            }
            $request->sort_date === 'oldest' ? $spacesQuery->oldest() : $spacesQuery->latest();
        } else {
            $spacesQuery->latest();
        }
        $spaces = $spacesQuery->get();

        return view('owner.spaces.index', compact('registrations', 'spaces', 'activeTab'));
    }

    public function show(Space $space)
    {
        if ($space->owner_id !== Auth::id()) abort(403);
        
        $space->load(['location', 'status', 'registration.prices.pricingType', 'photos', 'registration.photos']);
        return view('owner.spaces.show', compact('space'));
    }

    public function edit(Space $space)
    {
        if ($space->owner_id !== Auth::id()) abort(403);
        
        $pricingTypes = PricingType::all();
        $space->load(['location', 'registration.prices', 'photos', 'registration.photos']);
        
        return view('owner.spaces.edit', compact('space', 'pricingTypes'));
    }

    public function update(Request $request, Space $space)
    {
        if ($space->owner_id !== Auth::id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'dimension_type' => 'required|in:exact,total',
            'length' => 'nullable|numeric|required_if:dimension_type,exact',
            'width' => 'nullable|numeric|required_if:dimension_type,exact',
            'area' => 'nullable|numeric|required_if:dimension_type,total',
            'pricing' => 'required|array',
            'city' => 'required|string',
            'province' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            // 1. Update Location
            $space->location->update([
                'city' => $request->city,
                'province' => $request->province,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // 2. Calculate Final Area
            $calculatedArea = $request->dimension_type === 'exact' 
                ? ($request->length * $request->width) 
                : $request->area;

            // 3. Update Space & Registration
            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'length' => $request->dimension_type === 'exact' ? $request->length : null,
                'width' => $request->dimension_type === 'exact' ? $request->width : null,
                'area' => $calculatedArea,
            ];
            $space->update($updateData);
            $space->registration->update($updateData);

            // 4. Sync Pricing
            $space->registration->prices()->delete(); 
            $basePrice = null;
            
            foreach ($request->pricing as $typeId => $pricingData) {
                if (isset($pricingData['is_active']) && $pricingData['is_active'] == '1' && !empty($pricingData['price'])) {
                    $priceValue = $pricingData['price'];
                    
                    if (is_null($basePrice) || $priceValue < $basePrice) {
                        $basePrice = $priceValue;
                    }

                    $space->registration->prices()->create([
                        'pricing_type_id' => $typeId,
                        'price' => $priceValue,
                    ]);
                }
            }

            if (!is_null($basePrice)) {
                $space->update(['price' => $basePrice]);
            }

            // 5. Gallery Management: Deletions
            if ($request->filled('deleted_photos')) {
                $deletedIds = explode(',', $request->deleted_photos);
                $photosToDelete = SpacePhoto::whereIn('id', $deletedIds)
                    ->where('space_registration_id', $space->registration_id)
                    ->get();
                    
                foreach($photosToDelete as $photo) {
                    Storage::disk('public')->delete($photo->file_path);
                    $photo->delete();
                }
            }

            // 6. Gallery Management: Uploads
            if ($request->hasFile('new_photos')) {
                foreach ($request->file('new_photos') as $photoFile) {
                    $photoPath = $photoFile->store("spaces/gallery/reg_{$space->registration_id}", 'public');
                    SpacePhoto::create([
                        'space_registration_id' => $space->registration_id,
                        'space_id' => $space->id,
                        'file_path' => $photoPath,
                        'is_primary' => false, 
                    ]);
                }
            }

            // 7. Gallery Management: Primary Cover Setup
            if ($request->filled('primary_photo_id') && $request->primary_photo_id !== 'null') {
                SpacePhoto::where('space_registration_id', $space->registration_id)->update(['is_primary' => false]);
                SpacePhoto::where('id', $request->primary_photo_id)
                    ->where('space_registration_id', $space->registration_id)
                    ->update(['is_primary' => true]);
            } else {
                // Failsafe: if no primary is selected, pick the first available photo
                $firstPhoto = SpacePhoto::where('space_registration_id', $space->registration_id)->first();
                if ($firstPhoto) {
                    $firstPhoto->update(['is_primary' => true]);
                }
            }

            DB::commit();

            return redirect()->route('owner.spaces.show', $space->id)->with('success', 'Space details updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Update Failed: ' . $e->getMessage());
        }
    }

   public function updateStatus(Request $request, Space $space)
    {
        // 1. Security Check
        if ($space->owner_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:pause,unpause,unlist'
        ]);

        // 2. Handle Pause
        if ($request->action === 'pause') {
            $space->update(['status_id' => \App\Models\Status::where('code', 'spc_paused')->value('id')]);
            return back()->with('success', 'Space paused. It is temporarily hidden from the public marketplace.');
        }

        // 3. Handle Unpause
        if ($request->action === 'unpause') {
            $space->update(['status_id' => \App\Models\Status::where('code', 'spc_available')->value('id')]);
            return back()->with('success', 'Space reactivated! It is now live on the marketplace.');
        }

        // 4. Handle Unlist (Archiving)
        if ($request->action === 'unlist') {
            
            // TODO: do rent flow to so we can check unlist logic, cannot be done during an on going rent.
            /*
            $hasActiveRents = \App\Models\Rent::where('space_id', $space->id)
                ->whereHas('status', function ($query) {
                    // Prevent unlisting if there are pending requests or ongoing rentals
                    $query->whereIn('code', ['rnt_req_pending', 'rnt_ongoing']);
                })->exists();

            if ($hasActiveRents) {
                return back()->with('error', 'Action denied: You cannot unlist a space that has pending requests or ongoing active rentals. Please resolve them first.');
            }
            */

            $space->update(['status_id' => \App\Models\Status::where('code', 'spc_unlisted')->value('id')]);
            
            // Redirect back to dashboard since the space is now gone from active management
            return redirect()->route('owner.spaces.index')->with('success', 'Space permanently unlisted and archived.');
        }
    }
}