<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{ globalPreview: null }">
        
        <div class="mb-8 bg-indigo-50 border border-indigo-100 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-lg shadow-inner">👁️</div>
                <div>
                    <h2 class="text-sm font-black text-indigo-900 tracking-wide uppercase">Preview Mode</h2>
                    <p class="text-xs text-indigo-700 font-medium">This is exactly how renters see your property on the public marketplace.</p>
                </div>
            </div>
            <div class="flex gap-3 w-full sm:w-auto">
                <a href="{{ route('owner.spaces.index') }}" class="flex-1 sm:flex-none text-center bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-bold transition">Back to Dashboard</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-900 text-sm font-bold shadow-sm flex items-center gap-3">
                <span class="text-xl">✅</span> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-8 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-900 text-sm font-bold shadow-sm flex items-center gap-3">
                <span class="text-xl">⚠️</span> {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">{{ $space->name }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-gray-600">
                    <span class="flex items-center gap-1.5"><span class="text-teal-600">📍</span> {{ $space->location->address }}, {{ $space->location->city }}</span>
                    <span class="text-gray-300 hidden sm:inline">|</span>
                    <span class="flex items-center gap-1.5"><span class="text-teal-600">📏</span> {{ $space->formatted_size }}</span>
                </div>
            </div>
        </div>

        @php 
            $photos = $space->photos->count() > 0 ? $space->photos : $space->registration->photos; 
            $coverUrl = $photos->count() > 0 ? asset('storage/' . ($photos->where('is_primary', true)->first()->file_path ?? $photos->first()->file_path)) : '';
        @endphp

        <div class="mb-10 bg-white p-6 md:p-8 rounded-[2rem] border border-gray-100 shadow-sm" x-data="spaceGallery('{{ $coverUrl }}')">
            
            <div class="flex justify-between items-end mb-6 border-b border-gray-50 pb-4">
                <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-gray-50 text-gray-600 flex items-center justify-center text-sm">📸</span>
                    Property Gallery
                </h3>
            </div>

            @if($photos->count() > 0)
                <div class="w-full h-[350px] md:h-[450px] rounded-3xl overflow-hidden relative mb-4 bg-gray-100 group cursor-pointer" @click="globalPreview = { url: activeImageUrl, type: 'image' }">
                    <img :src="activeImageUrl" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition"></div>
                    <div class="absolute bottom-4 right-4 bg-black/50 backdrop-blur text-white text-xs font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition">
                        Click to enlarge
                    </div>
                </div>

                <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                    @foreach($photos->sortByDesc('is_primary') as $photo)
                        @php $photoUrl = asset('storage/' . $photo->file_path); @endphp
                        
                        <div @click="activeImageUrl = '{{ $photoUrl }}'"
                             :class="activeImageUrl === '{{ $photoUrl }}' ? 'ring-4 ring-orange-500 ring-offset-2 opacity-100' : 'opacity-60 hover:opacity-100'"
                             class="relative w-20 h-20 md:w-24 md:h-24 flex-shrink-0 rounded-2xl overflow-hidden transition-all bg-gray-100 cursor-pointer border border-gray-200">
                            
                            <img src="{{ $photoUrl }}" class="w-full h-full object-cover pointer-events-none">
                            
                            @if($photo->is_primary)
                                <div class="absolute top-1 left-1 bg-orange-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm tracking-wider uppercase z-10">
                                    COVER
                                </div>
                            @endif
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
            
            <div class="lg:col-span-2 space-y-12">
                
                <div class="flex items-center gap-4 pb-8 border-b border-gray-100">
                    <div class="w-16 h-16 bg-teal-100 text-teal-700 rounded-full flex items-center justify-center text-2xl font-black shadow-inner">
                        {{ substr($space->owner->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Hosted by {{ $space->owner->name }} (You)</h3>
                        <p class="text-sm text-gray-500 font-medium">Verified Owner ✅</p>
                    </div>
                </div>

                <div class="pb-8 border-b border-gray-100">
                    <h3 class="text-xl font-black text-gray-900 mb-4">About this space</h3>
                    <div class="prose prose-gray max-w-none text-gray-600 font-medium leading-relaxed whitespace-pre-line">
                        {{ $space->description }}
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-black text-gray-900 mb-4">Location</h3>
                    <div id="public-map" class="w-full h-[400px] rounded-[2rem] border border-gray-200 shadow-sm z-0"></div>
                </div>
            </div>

            <div class="relative">
                <div class="sticky top-8 bg-white p-8 rounded-[2rem] border border-gray-200 shadow-xl shadow-gray-100/50">
                    
                    <div class="mb-6">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">Current Status</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $space->status->code === 'spc_available' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                            @if($space->status->code === 'spc_available')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live on Marketplace
                            @elseif($space->status->code === 'spc_paused')
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Paused
                            @elseif($space->status->code === 'spc_unlisted')
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Unlisted
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ $space->status->name }}
                            @endif
                        </span>
                    </div>

                    <div class="mb-6 border-b border-gray-100 pb-6">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">Base Price</span>
                        <span class="text-3xl font-black text-gray-900">Rp {{ number_format($space->price, 0, ',', '.') }}</span>
                    </div>

                    <div class="space-y-3 mb-8">
                        <h4 class="text-[10px] font-black uppercase tracking-wider text-gray-400">All Configured Rates</h4>
                        @foreach($space->registration->prices as $price)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-sm font-bold text-gray-600">{{ $price->pricingType->name }}</span>
                                <span class="text-sm font-black text-gray-900">Rp {{ number_format($price->price, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- OWNER ACTIONS --}}
                    <div class="space-y-3 pt-6 border-t border-gray-100">
                        @if($space->status->code === 'spc_unlisted')
                            <div class="bg-red-50 border border-red-100 p-6 rounded-2xl text-center shadow-sm">
                                <span class="text-3xl mb-3 block">🚫</span>
                                <h4 class="font-black text-gray-900 mb-1">Permanently Unlisted</h4>
                                <p class="text-xs font-medium text-gray-600">This property has been permanently removed from the marketplace. It cannot be edited or republished.</p>
                            </div>
                        @else
                            {{-- Edit Space button stays as long as it's not unlisted --}}
                            <a href="{{ route('owner.spaces.edit', $space->id) }}" class="w-full bg-teal-600 hover:bg-teal-700 text-white text-center py-4 rounded-2xl font-black transition-all active:scale-95 flex items-center justify-center gap-2 shadow-lg shadow-teal-600/30">
                                ✎ Edit Space Details
                            </a>
                            
                            @if($space->status->code === 'spc_available')
                                <form action="{{ route('owner.spaces.status.update', $space->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="pause">
                                    <button type="submit" class="w-full bg-white border-2 border-gray-200 hover:border-gray-900 hover:bg-gray-900 hover:text-white text-gray-900 text-center py-3.5 rounded-2xl font-black transition-all active:scale-95 flex items-center justify-center gap-2">
                                        ⏸️ Pause Listing
                                    </button>
                                </form>
                            @elseif($space->status->code === 'spc_paused')
                                <form action="{{ route('owner.spaces.status.update', $space->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="unpause">
                                    <button type="submit" class="w-full bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 border border-emerald-100 text-center py-3.5 rounded-2xl font-black transition-all active:scale-95 flex items-center justify-center gap-2">
                                        ▶️ Republish Space
                                    </button>
                                </form>
                            @endif

                            @php
                                $activeStatuses = \App\Models\Status::whereIn('code', ['rnt_req_pending', 'rnt_awaiting_payment', 'rnt_ongoing'])->pluck('id');
                                $hasOngoingRent = \App\Models\RentRequest::where('space_id', $space->id)->whereIn('status_id', $activeStatuses)->exists();
                            @endphp

                            @if(!$hasOngoingRent)
                                <form action="{{ route('owner.spaces.status.update', $space->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently unlist this space? This cannot be undone easily.');">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="unlist">
                                    <button type="submit" class="w-full mt-2 bg-red-50 hover:bg-red-500 hover:text-white text-red-600 border border-red-100 text-center py-3.5 rounded-2xl font-black transition-all active:scale-95 flex items-center justify-center gap-2">
                                        🗑️ Unlist Property
                                    </button>
                                </form>
                            @else
                                <div class="mt-4 p-4 bg-orange-50 border border-orange-100 rounded-2xl text-center shadow-sm">
                                    <p class="text-[10px] font-black text-orange-600 uppercase tracking-wider flex items-center justify-center gap-1"><span>⚠️</span> Cannot Unlist</p>
                                    <p class="text-xs text-orange-800 font-medium mt-1">This property has active or pending rents. You must resolve them before unlisting.</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

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