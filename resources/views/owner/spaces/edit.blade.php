<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <a href="{{ route('owner.spaces.show', $space->id) }}" class="text-sm font-bold text-gray-400 hover:text-teal-600 transition mb-4 inline-flex items-center gap-2">&larr; Back to Preview</a>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight mt-2">Edit Space Details</h1>
        </div>

        @if($errors->any())
            <div class="mb-8 p-6 bg-red-50 border border-red-100 rounded-3xl text-red-800 text-sm shadow-sm">
                <div class="font-black mb-2 flex items-center gap-2"><span>⚠️</span> Please correct the errors below:</div>
                <ul class="list-disc list-inside space-y-1 text-xs font-medium text-red-700">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('owner.spaces.update', $space->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- 1. Basic Info --}}
            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <h3 class="text-lg font-black text-gray-900 mb-6 border-b border-gray-50 pb-4">📝 Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Listing Name *</label>
                        <input type="text" name="name" value="{{ old('name', $space->name) }}" required class="w-full bg-gray-50 border border-gray-200 focus:border-teal-500 rounded-2xl px-4 py-3.5 text-sm font-medium text-gray-900">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Description *</label>
                        <textarea name="description" rows="5" required class="w-full bg-gray-50 border border-gray-200 focus:border-teal-500 rounded-2xl px-4 py-3.5 text-sm font-medium text-gray-900">{{ old('description', $space->description) }}</textarea>
                    </div>

                    <div class="md:col-span-2 pt-4" x-data="{ 
                            dimensionType: '{{ old('dimension_type', $space->length ? 'exact' : 'total') }}', 
                            length: '{{ old('length', $space->length) }}', 
                            width: '{{ old('width', $space->width) }}', 
                            totalArea: '{{ old('area', $space->area) }}' 
                         }">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Space Dimensions *</label>
                        <input type="hidden" name="dimension_type" x-model="dimensionType">

                        <div class="flex gap-2 mb-4">
                            <button type="button" @click="dimensionType = 'exact'" :class="dimensionType === 'exact' ? 'bg-teal-600 text-white shadow-md' : 'bg-gray-100 text-gray-500'" class="px-4 py-2 rounded-xl text-xs font-bold">📏 Length x Width</button>
                            <button type="button" @click="dimensionType = 'total'" :class="dimensionType === 'total' ? 'bg-teal-600 text-white shadow-md' : 'bg-gray-100 text-gray-500'" class="px-4 py-2 rounded-xl text-xs font-bold">📐 Total Area Only</button>
                        </div>

                        <div x-show="dimensionType === 'exact'" x-cloak class="p-5 bg-gray-50 rounded-2xl border border-gray-200 flex items-center gap-3">
                            <input type="number" step="0.1" name="length" x-model="length" placeholder="Length" class="w-full bg-white border border-gray-200 focus:border-teal-500 rounded-xl px-4 py-3 text-sm">
                            <span class="text-gray-400 font-black">×</span>
                            <input type="number" step="0.1" name="width" x-model="width" placeholder="Width" class="w-full bg-white border border-gray-200 focus:border-teal-500 rounded-xl px-4 py-3 text-sm">
                        </div>

                        <div x-show="dimensionType === 'total'" x-cloak class="p-5 bg-gray-50 rounded-2xl border border-gray-200">
                            <input type="number" step="0.1" name="area" x-model="totalArea" placeholder="Total Area (m²)" class="w-full max-w-sm bg-white border border-gray-200 focus:border-teal-500 rounded-xl px-4 py-3 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Pricing --}}
            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <h3 class="text-lg font-black text-gray-900 mb-6 border-b border-gray-50 pb-4">💰 Rental Rates</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($pricingTypes as $type)
                        @php
                            $existingPrice = $space->registration->prices->where('pricing_type_id', $type->id)->first();
                            $isActive = $existingPrice ? 'true' : 'false';
                            $priceValue = $existingPrice ? $existingPrice->price : '';
                            $formattedInitial = $priceValue ? number_format($priceValue, 0, ',', '.') : '';
                        @endphp
                        
                        <div x-data="{ active: {{ $isActive }}, rawPrice: '{{ $priceValue }}', formattedPrice: '{{ $formattedInitial }}' }" 
                             class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                            <label class="flex items-center gap-3 cursor-pointer mb-3">
                                <input type="checkbox" x-model="active" name="pricing[{{ $type->id }}][is_active]" value="1" class="w-5 h-5 text-teal-600 rounded focus:ring-teal-500">
                                <span class="text-sm font-bold text-gray-900">{{ $type->name }} Rate</span>
                            </label>
                            <div x-show="active" x-cloak>
                                <div class="relative">
                                    <span class="absolute left-4 top-3.5 text-sm font-bold text-gray-400">Rp</span>
                                    <input type="text" x-model="formattedPrice" 
                                           @input="rawPrice = $event.target.value.replace(/\D/g, ''); formattedPrice = rawPrice ? new Intl.NumberFormat('id-ID').format(rawPrice) : ''" 
                                           class="w-full bg-white border border-gray-200 focus:border-teal-500 rounded-xl pl-12 pr-4 py-3 text-sm font-black" x-bind:required="active">
                                    <input type="hidden" name="pricing[{{ $type->id }}][price]" :value="rawPrice">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 3. Photo Gallery Manager (Cumulative Uploads + Preview) --}}
            @php
                $photos = $space->photos->count() > 0 ? $space->photos : $space->registration->photos;
                $coverId = $photos->where('is_primary', true)->first()->id ?? ($photos->first()->id ?? null);
            @endphp

            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-sm" x-data="editGalleryManager({{ $photos->toJson() }}, {{ $coverId ? $coverId : 'null' }})">
                
                <h3 class="text-lg font-black text-gray-900 mb-6 border-b border-gray-50 pb-4 flex justify-between items-center">
                    <span class="flex items-center gap-2">
                        📸 Photo Gallery Organizer 
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden sm:inline-block ml-2">(Drag to reorder - Leftmost is Cover)</span>
                    </span>
                    <button type="button" @click="$refs.triggerInput.click()" class="text-xs bg-teal-50 text-teal-600 px-4 py-2 rounded-xl font-bold hover:bg-teal-100 transition shadow-sm">+ Add New Photos</button>
                    
                    {{-- Hidden trigger for UI click --}}
                    <input type="file" multiple accept="image/*" class="hidden" x-ref="triggerInput" @change="handleNewFiles">
                    {{-- The actual input that holds the cumulative files for form submission --}}
                    <input type="file" name="new_photos[]" id="final-photos-input" multiple class="hidden" x-ref="finalInput">
                </h3>

                <input type="hidden" name="deleted_photos" :value="deletedIds.join(',')">
                <input type="hidden" name="primary_photo_id" :value="primaryId">

                {{-- Large Active Preview --}}
                <div class="w-full h-[350px] md:h-[450px] rounded-3xl overflow-hidden relative mb-4 bg-gray-100 flex items-center justify-center">
                    <img x-show="activePreviewUrl" :src="activePreviewUrl" class="w-full h-full object-cover">
                    <span x-show="!activePreviewUrl" class="text-gray-400 font-bold text-lg">No images available</span>
                </div>

                {{-- Draggable Thumbnails Grid --}}
                <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide" x-ref="sortableList">
                    
                    {{-- Existing Database Photos (Draggable) --}}
                    <template x-for="photo in activePhotos" :key="photo.id">
                        <div :data-id="photo.id" 
                             class="thumbnail-item relative w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer transition-all border-2"
                             :class="[
                                activePreviewUrl === photo.url ? 'border-teal-500' : 'border-transparent hover:border-gray-300',
                                primaryId == photo.id ? 'ring-4 ring-orange-500 ring-offset-2' : ''
                             ]"
                             @click="activePreviewUrl = photo.url">
                             
                            <img :src="photo.url" class="w-full h-full object-cover pointer-events-none">
                            <div x-show="primaryId == photo.id" class="absolute top-1 left-1 bg-orange-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm uppercase z-10">Cover</div>
                            
                            <div class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center gap-2 opacity-0 hover:opacity-100 transition z-20">
                                <button type="button" @click.stop="setPrimary(photo.id)" x-show="primaryId != photo.id" class="text-[9px] bg-white text-gray-900 font-bold px-2 py-1 rounded shadow-sm">Make Cover</button>
                                <button type="button" @click.stop="deletePhoto(photo.id)" class="w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs shadow-sm">✕</button>
                            </div>
                        </div>
                    </template>

                    {{-- Newly Added Files (Previewable & Removable) --}}
                    <template x-for="(file, index) in newPreviews" :key="'new-'+index">
                        <div class="relative w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-2xl overflow-hidden cursor-pointer border-2 border-dashed transition-all"
                             :class="activePreviewUrl === file.url ? 'border-teal-500 opacity-100' : 'border-teal-300 opacity-80 hover:opacity-100'"
                             @click="activePreviewUrl = file.url">
                             
                            <img :src="file.url" class="w-full h-full object-cover pointer-events-none">
                            
                            <div class="absolute inset-0 bg-teal-500/10 flex items-center justify-center pointer-events-none">
                                <span class="text-white text-[9px] font-black drop-shadow-md bg-teal-600 px-1.5 py-0.5 rounded uppercase">New</span>
                            </div>

                            <div class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center gap-2 opacity-0 hover:opacity-100 transition z-20">
                                <button type="button" @click.stop="removeNewFile(index)" class="w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs shadow-sm">✕</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 4. Location Map --}}
            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <h3 class="text-lg font-black text-gray-900 mb-6 border-b border-gray-50 pb-4">📍 Location Mapping</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">City</label>
                        <input type="text" name="city" value="{{ old('city', $space->location->city) }}" required class="w-full bg-gray-50 border border-gray-200 focus:border-teal-500 rounded-2xl px-4 py-3.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Province</label>
                        <input type="text" name="province" value="{{ old('province', $space->location->province) }}" required class="w-full bg-gray-50 border border-gray-200 focus:border-teal-500 rounded-2xl px-4 py-3.5 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Full Address</label>
                        <input type="text" name="address" value="{{ old('address', $space->location->address) }}" required class="w-full bg-gray-50 border border-gray-200 focus:border-teal-500 rounded-2xl px-4 py-3.5 text-sm">
                    </div>
                    
                    <div class="md:col-span-2 mt-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Pinpoint Map</label>
                        <p class="text-xs text-gray-400 mb-2">Drag the marker to update your exact location.</p>
                        <div id="map" class="w-full h-[300px] rounded-3xl border border-gray-200 z-0 relative"></div>
                        <div class="flex gap-4 mt-3 opacity-50">
                            <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $space->location->latitude) }}" required readonly class="w-full bg-gray-100 border-none rounded-xl text-xs font-mono">
                            <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $space->location->longitude) }}" required readonly class="w-full bg-gray-100 border-none rounded-xl text-xs font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2 pb-12">
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 active:scale-95 text-white font-black text-lg py-4 px-12 rounded-2xl shadow-xl shadow-teal-600/30 transition-all">Save All Updates</button>
            </div>
        </form>
    </div>

    {{-- Dependencies --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    {{-- Alpine Logic for Edit Gallery & Map --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('editGalleryManager', (initialPhotos, initialCoverId) => ({
                photos: initialPhotos.map(p => ({ id: p.id, url: '/storage/' + p.file_path })),
                deletedIds: [],
                primaryId: initialCoverId,
                activePreviewUrl: null,
                
                // Cumulative File Arrays
                newFiles: [],
                newPreviews: [],

                init() {
                    const coverPhoto = this.photos.find(p => p.id == this.primaryId) || this.photos[0];
                    if (coverPhoto) this.activePreviewUrl = coverPhoto.url;

                    let el = this.$refs.sortableList;
                    if (el) {
                        Sortable.create(el, {
                            animation: 200,
                            ghostClass: 'opacity-40',
                            draggable: '.thumbnail-item',
                            onEnd: (evt) => {
                                let items = el.querySelectorAll('.thumbnail-item');
                                if (items.length > 0) {
                                    let firstId = items[0].dataset.id;
                                    if (firstId) this.primaryId = parseInt(firstId);
                                }
                            }
                        });
                    }
                },

                get activePhotos() {
                    return this.photos.filter(p => !this.deletedIds.includes(p.id));
                },

                setPrimary(id) {
                    this.primaryId = id;
                },

deletePhoto(id) {
                    // 1. Find the exact URL of the photo being deleted
                    const photoToDelete = this.photos.find(p => p.id === id);
                    
                    // 2. Mark as deleted
                    this.deletedIds.push(id);
                    
                    // 3. Reassign cover photo if needed
                    if (this.primaryId == id) {
                        this.primaryId = this.activePhotos.length > 0 ? this.activePhotos[0].id : null;
                    }
                    
                    // 4. Update the big preview if we were currently looking at the deleted photo
                    if (photoToDelete && this.activePreviewUrl === photoToDelete.url) { 
                        if (this.activePhotos.length > 0) {
                            this.activePreviewUrl = this.activePhotos[0].url;
                        } else if (this.newPreviews.length > 0) {
                            this.activePreviewUrl = this.newPreviews[0].url;
                        } else {
                            this.activePreviewUrl = null;
                        }
                    }
                },

                handleNewFiles(event) {
                    const incomingFiles = Array.from(event.target.files);
                    if (incomingFiles.length === 0) return;

                    incomingFiles.forEach(file => {
                        this.newFiles.push(file);
                        this.newPreviews.push({ url: URL.createObjectURL(file), name: file.name });
                    });

                    // Set preview to the first newly added image if nothing is selected
                    if (!this.activePreviewUrl && this.newPreviews.length > 0) {
                        this.activePreviewUrl = this.newPreviews[0].url;
                    }

                    this.syncNewFilesToInput();
                    event.target.value = '';
                },

                removeNewFile(index) {
                    // 1. Store the URL before removing
                    const urlBeingDeleted = this.newPreviews[index].url;

                    // 2. Remove the file
                    this.newFiles.splice(index, 1);
                    this.newPreviews.splice(index, 1);
                    
                    // 3. Update the big preview if we were looking at it
                    if (this.activePreviewUrl === urlBeingDeleted) {
                        if (this.activePhotos.length > 0) {
                            this.activePreviewUrl = this.activePhotos[0].url;
                        } else if (this.newPreviews.length > 0) {
                            this.activePreviewUrl = this.newPreviews[0].url;
                        } else {
                            this.activePreviewUrl = null;
                        }
                    }
                    
                    this.syncNewFilesToInput();
                },
                syncNewFilesToInput() {
                    const dataTransfer = new DataTransfer();
                    this.newFiles.forEach(file => dataTransfer.items.add(file));
                    this.$refs.finalInput.files = dataTransfer.files;
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            const latInput = document.getElementById("latitude");
            const lngInput = document.getElementById("longitude");
            
            const lat = latInput.value || -6.28862;
            const lng = lngInput.value || 106.71789;

            const map = L.map('map').setView([lat, lng], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

            const marker = L.marker([lat, lng], { draggable: true }).addTo(map);

            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                latInput.value = position.lat.toFixed(7);
                lngInput.value = position.lng.toFixed(7);
            });
            
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                latInput.value = e.latlng.lat.toFixed(7);
                lngInput.value = e.latlng.lng.toFixed(7);
            });
        });
    </script>
</x-user-layout>