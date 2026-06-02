<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="{ globalPreview: null }">
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight mt-3">List Your Space</h1>
            <p class="text-sm text-gray-500 mt-2 max-w-xl mx-auto">
                Register your unused spot, booth, or ruko legally. Once approved by our moderation team, your space will go live on the public discovery catalog.
            </p>
        </div>

        @if(session('error'))
            <div class="mb-8 p-6 bg-red-50 border border-red-200 rounded-3xl text-red-800 text-sm shadow-sm flex items-center gap-3">
                <span class="text-2xl">🚨</span> 
                <div>
                    <span class="font-black block">Transaction Failed!</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-8 p-6 bg-emerald-50 border border-emerald-100 rounded-[2rem] text-emerald-900 text-sm shadow-sm flex items-center gap-3 animate-fade-in">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-emerald-500/20">
                    ✓
                </div>
                <div>
                    <span class="font-black block text-base">Success!</span>
                    <span class="font-medium text-emerald-700 text-xs">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 p-6 bg-red-50 border border-red-100 rounded-3xl text-red-800 text-sm shadow-sm">
                <div class="font-black mb-2 flex items-center gap-2">
                    <span>⚠️</span> Please correct the errors below:
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs font-medium text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('space-registrations.store') }}" method="POST" enctype="multipart/form-data" id="space-form" class="space-y-10">
            @csrf

            {{-- 1. Basic Information & Pricing --}}
            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm font-bold">1</span>
                    Basic Information & Pricing
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Listing Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               placeholder="e.g., Lapak Strategis Depan Kampus" 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Description *</label>
                        <textarea name="description" rows="4" required 
                                  placeholder="Provide key details about foot traffic, nearby landmarks..." 
                                  class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">{{ old('description') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Dimensions / Size *</label>
                        <input type="text" name="size" value="{{ old('size') }}" required 
                               placeholder="e.g., 2x2 Meter / 3x4 m" 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-4">Rental Rates (Select at least one) *</label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @forelse($pricingTypes as $type)
                                <div x-data="{ active: false, rawPrice: '', formattedPrice: '' }" class="p-4 bg-gray-50 rounded-2xl border border-gray-200 transition-all" :class="active ? 'border-orange-500 ring-1 ring-orange-200' : ''">
                                    <label class="flex items-center gap-3 cursor-pointer mb-3">
                                        <input type="checkbox" x-model="active" name="pricing[{{ $type->id }}][is_active]" value="1" class="w-5 h-5 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                                        <span class="text-sm font-bold text-gray-900">{{ $type->name }} Rate</span>
                                    </label>
                                    
                                    <div x-show="active" x-cloak>
                                        <div class="relative">
                                            <span class="absolute left-4 top-3.5 text-sm font-bold text-gray-400">Rp</span>
                                            <input type="text" x-model="formattedPrice" 
                                                   @input="rawPrice = $event.target.value.replace(/\D/g, ''); formattedPrice = rawPrice ? new Intl.NumberFormat('id-ID').format(rawPrice) : ''"
                                                   placeholder="0" 
                                                   class="w-full bg-white border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl pl-12 pr-4 py-3 text-sm outline-none transition font-black text-gray-900"
                                                   x-bind:required="active">
                                            <input type="hidden" name="pricing[{{ $type->id }}][price]" :value="rawPrice">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-2 p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl text-xs font-bold">
                                    Pricing types not found! Please run your seeders.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Location Mapping --}}
            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm font-bold">2</span>
                    Location Mapping
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">City / Municipality *</label>
                        <input type="text" name="city" value="{{ old('city') }}" required placeholder="e.g., South Tangerang" class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 rounded-2xl px-4 py-3.5 text-sm outline-none font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Province *</label>
                        <input type="text" name="province" value="{{ old('province') }}" required placeholder="e.g., Banten" class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 rounded-2xl px-4 py-3.5 text-sm outline-none font-medium">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Full Address *</label>
                        <input type="text" name="address" value="{{ old('address') }}" required placeholder="Street Name, Building/Lot No., RT/RW, District..." class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 rounded-2xl px-4 py-3.5 text-sm outline-none font-medium">
                    </div>

                    <div class="md:col-span-2 mt-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pinpoint Location *</label>
                        <p class="text-[11px] text-gray-400 mb-3">Search for your address or drag the marker to pinpoint the exact location.</p>
                        
                        <div x-data="locationSearch()" class="relative mb-4 z-10">
                            <div class="flex gap-2">
                                <input type="text" x-model="query" @input.debounce.500ms="searchPlaces" @keydown.enter.prevent="searchPlaces"
                                       placeholder="Search for a place or street (e.g., Binus Kemanggisan)..." 
                                       class="w-full bg-white border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl px-4 py-2.5 text-sm outline-none font-medium text-gray-900 shadow-sm relative z-20">
                                <button type="button" @click="searchPlaces" class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-sm whitespace-nowrap z-20">
                                    <span x-show="!isLoading">Search Map</span>
                                    <span x-show="isLoading" x-cloak>Searching...</span>
                                </button>
                            </div>

                            <div x-show="showDropdown && suggestions.length > 0" @click.away="showDropdown = false" x-transition x-cloak
                                 class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-xl shadow-2xl z-50 max-h-60 overflow-y-auto overscroll-contain">
                                <ul class="divide-y divide-gray-50">
                                    <template x-for="place in suggestions" :key="place.properties.osm_id">
                                        <li @click="selectPlace(place)" class="p-3 hover:bg-orange-50 hover:text-orange-600 cursor-pointer transition flex flex-col justify-center">
                                            <p class="text-xs font-bold text-gray-900" x-text="place.properties.name || place.properties.street"></p>
                                            <p class="text-[10px] text-gray-500 mt-0.5 truncate" x-text="[place.properties.street, place.properties.city, place.properties.state].filter(Boolean).join(', ')"></p>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div id="map" class="w-full h-[350px] rounded-3xl border border-gray-200 shadow-sm z-0 relative"></div>
                        
                        <div class="flex gap-4 mt-3 opacity-50">
                            <input type="text" name="latitude" id="latitude" required readonly placeholder="Latitude" class="w-full bg-gray-100 border-none rounded-xl text-xs font-mono">
                            <input type="text" name="longitude" id="longitude" required readonly placeholder="Longitude" class="w-full bg-gray-100 border-none rounded-xl text-xs font-mono">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Legal Verification Assets --}}
            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                <h3 class="text-lg font-black text-gray-900 mb-2 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm font-bold">3</span>
                    Legal Verification Assets
                </h3>
                <p class="text-xs text-gray-400 mb-6 pl-10">We verify the legal authorization of all listing providers to keep the community safe.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Surat Kepemilikan Lahan / Sertifikat *</label>
                        <div x-data="singleDocManager()" class="h-40 relative bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200 transition group overflow-hidden" :class="fileUrl ? 'border-transparent border-none p-0' : 'hover:border-orange-500 p-6'">
                            
                            <div x-show="!fileUrl" @click="$refs.fileInput.click()" class="cursor-pointer h-full flex flex-col justify-center items-center text-center">
                                <div class="w-12 h-12 bg-white text-orange-500 rounded-2xl flex items-center justify-center mb-2 shadow-sm group-hover:scale-110 transition">📄</div>
                                <span class="text-xs font-bold text-gray-900 block">Upload Land Certificate</span>
                                <span class="text-[10px] text-gray-400 block mt-1">PDF, JPG or PNG (Max 5MB)</span>
                            </div>

                            <div x-show="fileUrl" x-cloak class="relative w-full h-full bg-white border border-gray-100 rounded-3xl overflow-hidden group">
                                <div class="absolute top-3 right-3 flex gap-1.5 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click.prevent="triggerReplace" class="bg-blue-500 hover:bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm shadow-md transition" title="Replace">🔄</button>
                                    <button type="button" @click.prevent="removeFile" class="bg-red-500 hover:bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md transition" title="Remove">✕</button>
                                </div>
                                <template x-if="!isPdf"><img :src="fileUrl" @click="globalPreview = { url: fileUrl, type: 'image' }" class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition"></template>
                                <template x-if="isPdf">
                                    <div @click="globalPreview = { url: fileUrl, type: 'pdf' }" class="w-full h-full bg-teal-50 flex flex-col items-center justify-center cursor-pointer hover:bg-teal-100 transition border border-teal-100">
                                        <span class="text-3xl mb-2">📑</span>
                                        <span class="text-xs font-black text-teal-700 max-w-[80%] truncate" x-text="fileName"></span>
                                        <span class="text-[10px] font-bold text-teal-500 mt-1">Click to View PDF</span>
                                    </div>
                                </template>
                            </div>
                            <input type="file" name="surat_tanah" x-ref="fileInput" @change="handleFile" class="hidden" accept=".pdf,image/*" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Surat Perjanjian Sewa / Izin Usaha <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <div x-data="singleDocManager()" class="h-40 relative bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200 transition group overflow-hidden" :class="fileUrl ? 'border-transparent border-none p-0' : 'hover:border-orange-500 p-6'">
                            
                            <div x-show="!fileUrl" @click="$refs.fileInput.click()" class="cursor-pointer h-full flex flex-col justify-center items-center text-center">
                                <div class="w-12 h-12 bg-white text-orange-500 rounded-2xl flex items-center justify-center mb-2 shadow-sm group-hover:scale-110 transition">🤝</div>
                                <span class="text-xs font-bold text-gray-900 block">Upload Secondary Agreement</span>
                                <span class="text-[10px] text-gray-400 block mt-1">Supporting Rental Contracts</span>
                            </div>

                            <div x-show="fileUrl" x-cloak class="relative w-full h-full bg-white border border-gray-100 rounded-3xl overflow-hidden group">
                                <div class="absolute top-3 right-3 flex gap-1.5 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click.prevent="triggerReplace" class="bg-blue-500 hover:bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm shadow-md transition" title="Replace">🔄</button>
                                    <button type="button" @click.prevent="removeFile" class="bg-red-500 hover:bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md transition" title="Remove">✕</button>
                                </div>
                                <template x-if="!isPdf"><img :src="fileUrl" @click="globalPreview = { url: fileUrl, type: 'image' }" class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition"></template>
                                <template x-if="isPdf">
                                    <div @click="globalPreview = { url: fileUrl, type: 'pdf' }" class="w-full h-full bg-teal-50 flex flex-col items-center justify-center cursor-pointer hover:bg-teal-100 transition border border-teal-100">
                                        <span class="text-3xl mb-2">📑</span>
                                        <span class="text-xs font-black text-teal-700 max-w-[80%] truncate" x-text="fileName"></span>
                                        <span class="text-[10px] font-bold text-teal-500 mt-1">Click to View PDF</span>
                                    </div>
                                </template>
                            </div>
                            <input type="file" name="surat_izin" x-ref="fileInput" @change="handleFile" class="hidden" accept=".pdf,image/*">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Property Showcase Gallery (UPDATED WITH COVER SELECTOR) --}}
            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40" x-data="galleryManager()">
                <h3 class="text-lg font-black text-gray-900 mb-2 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm font-bold">4</span>
                    Property Showcase Gallery
                </h3>
                <p class="text-xs text-gray-400 mb-6 pl-10">Upload multiple photos. Click the "MAKE COVER" button on an image to select it as the primary photo.</p>

                <div class="bg-gray-50 p-6 rounded-3xl border-2 border-dashed border-gray-200 text-center hover:border-orange-500 transition cursor-pointer" @click="$refs.fileInput.click()">
                    <div class="w-12 h-12 bg-white text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-sm">📸</div>
                    <span class="text-xs font-bold text-gray-900 block">Click to Add Images</span>
                    <input type="file" multiple accept="image/*" class="hidden" x-ref="fileInput" @change="addFiles($event)">
                </div>

                <input type="file" accept="image/*" class="hidden" x-ref="replaceInput" @change="handleReplace($event)">
                <input type="file" name="photos[]" id="final-photos" multiple class="hidden">
                
                {{-- Hidden input passing the chosen primary index to the backend --}}
                <input type="hidden" name="primary_photo_index" :value="primaryIndex">

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                    <template x-for="(image, index) in images" :key="image.id">
                        <div class="relative bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm animate-fade-in group"
                             :class="primaryIndex === index ? 'ring-2 ring-orange-500' : ''">
                            
                            {{-- Interactive Cover Selector Button --}}
                            <button type="button" 
                                    @click="primaryIndex = index"
                                    :class="primaryIndex === index ? 'bg-orange-500 text-white' : 'bg-white/90 text-gray-700 hover:bg-orange-500 hover:text-white backdrop-blur'"
                                    class="absolute top-2 left-2 text-[9px] font-extrabold px-2 py-1 rounded shadow z-20 transition-all uppercase tracking-wider">
                                <span x-text="primaryIndex === index ? '🌟 Cover' : 'Make Cover'"></span>
                            </button>

                            <div class="absolute top-2 right-2 flex gap-1 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click.stop="triggerReplace(index)" class="bg-blue-500 hover:bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-xs shadow-md transition" title="Replace Image">🔄</button>
                                <button type="button" @click.stop="removeFile(index)" class="bg-red-500 hover:bg-red-600 text-white w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shadow-md transition" title="Remove Image">✕</button>
                            </div>
                            
                            <img :src="image.url" class="w-full h-28 object-cover cursor-pointer hover:opacity-90 transition" @click="globalPreview = { url: image.url, type: 'image' }" alt="Preview">
                            
                            <div class="p-2 bg-gray-50 border-t border-gray-100">
                                <input type="text" :name="`photo_descriptions[${index}]`" placeholder="Image caption..." 
                                       class="w-full bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-[10px] outline-none focus:border-orange-500 font-medium text-gray-800">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" id="submit-button" class="bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-black text-base py-4 px-12 rounded-2xl shadow-xl shadow-orange-500/25 transition-all outline-none">
                    Submit Space Listing
                </button>
            </div>
        </form>

        {{-- Global Preview Modal --}}
        <div x-show="globalPreview !== null" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" @click.self="globalPreview = null" @keydown.escape.window="globalPreview = null">
            <div class="relative w-full max-w-4xl max-h-[95vh] bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col">
                <button type="button" @click="globalPreview = null" class="absolute top-4 right-4 bg-gray-900/80 hover:bg-gray-900 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg backdrop-blur transition z-10">✕</button>
                <div class="p-2 overflow-auto flex-grow flex items-center justify-center bg-gray-900 rounded-3xl">
                    <template x-if="globalPreview?.type === 'image'"><img :src="globalPreview.url" class="max-w-full max-h-[90vh] object-contain rounded-2xl" alt="Full Preview"></template>
                    <template x-if="globalPreview?.type === 'pdf'"><iframe :src="globalPreview.url" class="w-full h-[85vh] rounded-2xl bg-white border-0"></iframe></template>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            
            Alpine.data('locationSearch', () => ({
                query: '',
                suggestions: [],
                isLoading: false,
                showDropdown: false,

                searchPlaces() {
                    if (this.query.trim().length < 3) {
                        this.suggestions = [];
                        this.showDropdown = false;
                        return;
                    }
                    this.isLoading = true;
                    fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(this.query)}&limit=15&bbox=95.011,-11.008,141.019,6.077`)
                        .then(res => res.json())
                        .then(data => {
                            this.suggestions = data.features;
                            this.showDropdown = true;
                        })
                        .catch(err => console.error('Geocoding error:', err))
                        .finally(() => { this.isLoading = false; });
                },

                selectPlace(place) {
                    const lng = parseFloat(place.geometry.coordinates[0]);
                    const lat = parseFloat(place.geometry.coordinates[1]);
                    const name = place.properties.name || place.properties.street || 'Selected Location';
                    
                    this.query = name; 
                    this.showDropdown = false;
                    if (window.updateMapLocation) window.updateMapLocation(lat, lng);
                }
            }));

            Alpine.data('singleDocManager', () => ({
                fileUrl: null,
                fileName: '',
                isPdf: false,
                handleFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.isPdf = file.type === 'application/pdf';
                    this.fileUrl = URL.createObjectURL(file);
                    this.fileName = file.name;
                },
                removeFile() {
                    this.fileUrl = null;
                    this.fileName = '';
                    this.isPdf = false;
                    this.$refs.fileInput.value = ''; 
                },
                triggerReplace() { this.$refs.fileInput.click(); }
            }));

            // UPDATED: Gallery Manager tracking primaryIndex
            Alpine.data('galleryManager', () => ({
                images: [],
                dataTransfer: new DataTransfer(),
                replaceTargetIndex: null, 
                primaryIndex: 0, // Defaults to the first uploaded image

                addFiles(event) {
                    const files = event.target.files;
                    if (!files.length) return;
                    for(let i = 0; i < files.length; i++) {
                        const file = files[i];
                        this.dataTransfer.items.add(file);
                        this.images.push({
                            id: Date.now() + i + Math.random(), 
                            url: URL.createObjectURL(file),
                            file: file
                        });
                    }
                    document.getElementById('final-photos').files = this.dataTransfer.files;
                    event.target.value = ''; 
                },

                removeFile(index) {
                    this.images.splice(index, 1);
                    
                    // Smart index shifting if an image is deleted
                    if (this.primaryIndex === index) {
                        this.primaryIndex = 0; // Reset to the first available image
                    } else if (this.primaryIndex > index) {
                        this.primaryIndex--; // Shift left if a preceding image was removed
                    }

                    this.rebuildDataTransfer();
                },

                triggerReplace(index) {
                    this.replaceTargetIndex = index;
                    this.$refs.replaceInput.click();
                },

                handleReplace(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.images[this.replaceTargetIndex].url = URL.createObjectURL(file);
                    this.images[this.replaceTargetIndex].file = file;
                    this.rebuildDataTransfer();
                    event.target.value = ''; 
                    this.replaceTargetIndex = null;
                },

                rebuildDataTransfer() {
                    const newDt = new DataTransfer();
                    for(let i = 0; i < this.images.length; i++) {
                        newDt.items.add(this.images[i].file);
                    }
                    this.dataTransfer = newDt;
                    document.getElementById('final-photos').files = this.dataTransfer.files;
                }
            }));
        });

        // Initialize Map
        document.addEventListener('DOMContentLoaded', function() {
            const defaultLat = -6.28862; 
            const defaultLng = 106.71789;
            const latInput = document.getElementById("latitude");
            const lngInput = document.getElementById("longitude");
            
            latInput.value = defaultLat;
            lngInput.value = defaultLng;

            const map = L.map('map').setView([defaultLat, defaultLng], 13);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

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

            window.updateMapLocation = function(lat, lng) {
                map.setView([lat, lng], 16);
                marker.setLatLng([lat, lng]);
                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);
            };

            const form = document.getElementById('space-form');
            const submitBtn = document.getElementById('submit-button');
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    submitBtn.innerHTML = 'Buffering Assets...';
                });
            }
        });
    </script>
</x-user-layout>