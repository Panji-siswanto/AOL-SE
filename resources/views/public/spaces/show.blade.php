<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{ globalPreview: null }">
        
        {{-- Breadcrumb / Back Button --}}
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-teal-600 transition flex items-center gap-2">
                &larr; Back to Discovery
            </a>
        </div>

        {{-- Header Section --}}
        <div class="mb-6 flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">{{ $space->name }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-gray-600">
                    <span class="flex items-center gap-1.5"><span class="text-teal-600">📍</span> {{ $space->location->address }}, {{ $space->location->city }}</span>
                    <span class="text-gray-300 hidden sm:inline">|</span>
                    <span class="flex items-center gap-1.5"><span class="text-teal-600">📏</span> {{ $space->length && $space->width ? $space->length.'x'.$space->width.'m' : $space->area.'m²' }}</span>
                </div>
            </div>
        </div>

        {{-- Photo Gallery Slider --}}
        @php 
            $photos = $space->photos->count() > 0 ? $space->photos : $space->registration->photos; 
            $coverUrl = $photos->count() > 0 ? asset('storage/' . ($photos->where('is_primary', true)->first()->file_path ?? $photos->first()->file_path)) : '';
        @endphp

        <div class="mb-10 bg-white p-6 md:p-8 rounded-[2rem] border border-gray-100 shadow-sm" x-data="spaceGallery('{{ $coverUrl }}')">
            
            @if($photos->count() > 0)
                {{-- Main Active Image --}}
                <div class="w-full h-[350px] md:h-[450px] rounded-3xl overflow-hidden relative mb-4 bg-gray-100 group cursor-pointer" @click="globalPreview = { url: activeImageUrl, type: 'image' }">
                    <img :src="activeImageUrl" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition"></div>
                </div>

                {{-- Interactive Thumbnails --}}
                <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                    @foreach($photos->sortByDesc('is_primary') as $photo)
                        @php $photoUrl = asset('storage/' . $photo->file_path); @endphp
                        <div @click="activeImageUrl = '{{ $photoUrl }}'"
                             :class="activeImageUrl === '{{ $photoUrl }}' ? 'ring-4 ring-orange-500 ring-offset-2 opacity-100' : 'opacity-60 hover:opacity-100'"
                             class="relative w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-2xl overflow-hidden transition-all bg-gray-100 cursor-pointer border border-gray-200">
                            <img src="{{ $photoUrl }}" class="w-full h-full object-cover pointer-events-none">
                        </div>
                    @endforeach
                </div>
            @else
                <div class="w-full h-[300px] flex flex-col items-center justify-center text-gray-400 bg-gray-50 rounded-3xl">
                    <span class="text-5xl mb-3">🖼️</span>
                    <p class="font-bold text-sm">No photos available</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            
            {{-- Main Content (Left Column) --}}
            <div class="lg:col-span-2 space-y-12">
                
                {{-- Host Info --}}
                <div class="flex items-center gap-4 pb-8 border-b border-gray-100">
                    <div class="w-16 h-16 bg-teal-100 text-teal-700 rounded-full flex items-center justify-center text-2xl font-black shadow-inner">
                        {{ substr($space->owner->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Hosted by {{ $space->owner->name }}</h3>
                        @if($space->owner->ver_status == \App\Models\Status::where('code', 'usr_verified')->value('id'))
                            <p class="text-sm text-teal-600 font-bold flex items-center gap-1">✅ Verified Identity</p>
                        @endif
                    </div>
                </div>

                {{-- About This Space --}}
                <div class="pb-8 border-b border-gray-100">
                    <h3 class="text-xl font-black text-gray-900 mb-4">About this space</h3>
                    <div class="prose prose-gray max-w-none text-gray-600 font-medium leading-relaxed whitespace-pre-line">
                        {{ $space->description }}
                    </div>
                </div>

                {{-- Map --}}
                <div>
                    <h3 class="text-xl font-black text-gray-900 mb-4">Location</h3>
                    <div id="public-map" class="w-full h-[400px] rounded-[2rem] border border-gray-200 shadow-sm z-0"></div>
                </div>
            </div>

            {{-- Sticky Renter Checkout Widget (Right Column) --}}
            <div class="relative">
                <div class="sticky top-8 bg-white p-8 rounded-[2rem] border border-gray-200 shadow-xl shadow-gray-100/50">
                    
                    <div class="mb-6 border-b border-gray-100 pb-6">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">Starting at</span>
                        <div class="flex items-end gap-2">
                            <span class="text-3xl font-black text-gray-900">Rp {{ number_format($space->price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Form for future Renting --}}
                    <form action="#" method="GET" class="space-y-6">
                        
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-3">Available Rental Packages</label>
                            <div class="space-y-3">
                                @foreach($space->registration->prices as $price)
                                    <label class="flex justify-between items-center p-4 bg-gray-50 hover:bg-teal-50 rounded-xl border border-gray-200 hover:border-teal-300 cursor-pointer transition has-[:checked]:bg-teal-50 has-[:checked]:border-teal-500 has-[:checked]:ring-1 has-[:checked]:ring-teal-500">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="pricing_id" value="{{ $price->id }}" class="w-4 h-4 text-teal-600 focus:ring-teal-500 border-gray-300" required>
                                            <span class="text-sm font-bold text-gray-700">{{ $price->pricingType->name }}</span>
                                        </div>
                                        <span class="text-sm font-black text-gray-900">Rp {{ number_format($price->price, 0, ',', '.') }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <button type="button" onclick="alert('Checkout process coming soon!')" class="w-full bg-orange-500 hover:bg-orange-600 text-white text-center py-4 rounded-2xl font-black transition-all active:scale-95 shadow-lg shadow-orange-500/30">
                                Request to Rent
                            </button>
                            <p class="text-[10px] text-center text-gray-400 font-bold mt-4 uppercase tracking-wider">You won't be charged yet</p>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Global Preview Modal --}}
        <div x-show="globalPreview !== null" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-black/95 backdrop-blur-sm flex items-center justify-center p-4" @click.self="globalPreview = null" @keydown.escape.window="globalPreview = null">
            <button type="button" @click="globalPreview = null" class="absolute top-6 right-6 text-white text-4xl hover:scale-110 transition z-10">&times;</button>
            <template x-if="globalPreview?.type === 'image'">
                <img :src="globalPreview.url" class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl" alt="Full Preview">
            </template>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('spaceGallery', (initialUrl) => ({
                activeImageUrl: initialUrl
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            const lat = {{ $space->location->latitude ?? -6.28862 }};
            const lng = {{ $space->location->longitude ?? 106.71789 }};
            const map = L.map('public-map', {
                center: [lat, lng], zoom: 16, scrollWheelZoom: false, dragging: false, touchZoom: false, doubleClickZoom: false, boxZoom: false
            });
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            L.marker([lat, lng]).addTo(map);
        });
    </script>
</x-user-layout>