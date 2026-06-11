<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="{ globalPreview: null }">
        
        {{-- Breadcrumb & Alerts --}}
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-teal-600 transition flex items-center gap-2">
                &larr; Back to Discovery
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 font-bold text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="mb-6 bg-teal-50 text-teal-700 p-4 rounded-xl border border-teal-100 font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">{{ $space->name }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-gray-600">
                    <span class="flex items-center gap-1.5"><span class="text-teal-600">📍</span> {{ $space->location->address }}, {{ $space->location->city }}</span>
                    <span class="text-gray-300 hidden sm:inline">|</span>
                    <span class="flex items-center gap-1.5"><span class="text-teal-600">📏</span> {{ $space->length && $space->width ? $space->length.'x'.$space->width.'m' : $space->area.'m²' }}</span>
                </div>
            </div>
            
            <button @click.prevent="toggleBookmark"
                    x-data="{
                        bookmarked: {{ $isBookmarked ? 'true' : 'false' }},
                        loading: false,
                        toggleBookmark() {
                            @if(!auth()->check())
                                window.dispatchEvent(new CustomEvent('open-login-modal'));
                                return;
                            @endif
                            
                            if(this.loading) return;
                            this.loading = true;
                            
                            fetch('{{ route('spaces.bookmark', $space->id) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                this.bookmarked = data.bookmarked;
                                this.loading = false;
                            }).catch(() => this.loading = false);
                        }
                    }"
                    :class="bookmarked ? 'text-teal-600 bg-teal-50 border-teal-200' : 'text-gray-500 bg-white border-gray-200 hover:bg-gray-50'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border font-bold text-sm shadow-sm transition active:scale-95 w-fit">
                
                <svg x-show="!bookmarked" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 22.096c0 .514-.61.776-1.01.442L12 18.75l-4.49 3.788c-.4.334-1.01.072-1.01-.442V4.5A1.5 1.5 0 0 1 8 3h8a1.5 1.5 0 0 1 1.5 1.5v17.596Z" />
                </svg>
                
                <svg x-show="bookmarked" style="display: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M6.5 3A1.5 1.5 0 0 0 5 4.5v17.596c0 .514.61.776 1.01.442L12 18.75l4.49 3.788c.4.334 1.01.072 1.01-.442V4.5A1.5 1.5 0 0 0 16 3H6.5Z" clip-rule="evenodd" />
                </svg>
                <span x-text="bookmarked ? 'Saved' : 'Save'"></span>
            </button>
        </div>

        @php 
            $photos = $space->photos->count() > 0 ? $space->photos : $space->registration->photos; 
            $coverUrl = $photos->count() > 0 ? asset('storage/' . ($photos->where('is_primary', true)->first()->file_path ?? $photos->first()->file_path)) : '';
            
            // 🔥 NEW: Check globally if this space is already rented out
            $ongoingId = \App\Models\Status::where('code', 'rnt_ongoing')->value('id');
            $awaitingPaymentId = \App\Models\Status::where('code', 'rnt_awaiting_payment')->value('id');

            $activeBooking = \App\Models\RentRequest::where('space_id', $space->id)
                ->whereIn('status_id', [$ongoingId, $awaitingPaymentId])
                ->first();

            $isRentedByMe = $activeBooking && auth()->check() && $activeBooking->renter_id === auth()->id();
            $isRentedByOther = $activeBooking && (!auth()->check() || $activeBooking->renter_id !== auth()->id());
        @endphp

        <div class="mb-10 bg-white p-6 md:p-8 rounded-[2rem] border border-gray-100 shadow-sm" x-data="spaceGallery('{{ $coverUrl }}')">
            
            @if($photos->count() > 0)
                <div class="w-full h-[350px] md:h-[450px] rounded-3xl overflow-hidden relative mb-4 bg-gray-100 group cursor-pointer" @click="globalPreview = { url: activeImageUrl, type: 'image' }">
                    <img :src="activeImageUrl" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition"></div>
                </div>

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
            
            <div class="lg:col-span-2 space-y-12">
                
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
                    
                    <div class="mb-6 border-b border-gray-100 pb-6">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">Starting at</span>
                        <div class="flex items-end gap-2">
                            <span class="text-3xl font-black text-gray-900">Rp {{ number_format($space->price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- READ-ONLY PRICING DISPLAY --}}
                    <div class="mb-6">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-3">All Configured Rates</span>
                        <div class="space-y-2">
                            @foreach($space->registration->prices as $price)
                                <div class="flex justify-between items-center p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                                    <span class="text-sm font-bold text-gray-700">{{ $price->pricingType->name }}</span>
                                    <span class="text-sm font-black text-gray-900">Rp {{ number_format($price->price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 🔥 DYNAMIC RENT ACTIONS LOGIC --}}
                    @if(auth()->check() && auth()->id() === $space->owner_id)
                        <div class="bg-gray-50 border border-gray-200 p-6 rounded-2xl text-center mt-6">
                            <span class="text-3xl mb-3 block">🏠</span>
                            <h4 class="font-black text-gray-900 mb-1">Your Listing</h4>
                            <p class="text-sm font-medium text-gray-600 mb-4">You are the host of this space. You cannot request to rent it.</p>
                            <a href="{{ route('owner.spaces.show', $space->id) }}" class="w-full block bg-gray-900 hover:bg-black text-white py-3 rounded-xl font-bold transition-all shadow-sm text-center">
                                Manage Space
                            </a>
                        </div>
                    
                    @elseif($isRentedByMe)
                        <div class="bg-blue-50 border border-blue-100 p-6 rounded-2xl text-center mt-6">
                            <span class="text-3xl mb-3 block">🔑</span>
                            <h4 class="font-black text-gray-900 mb-1">You Are Renting This Space</h4>
                            <p class="text-sm font-medium text-blue-800 mb-4">You currently have an ongoing contract or are awaiting payment for this space.</p>
                            <a href="{{ route('rents.index') }}" class="w-full block bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold transition-all shadow-sm text-center">
                                Manage My Rent
                            </a>
                        </div>

                    @elseif($isRentedByOther)
                        <div class="bg-gray-50 border border-gray-200 p-6 rounded-2xl text-center mt-6">
                            <span class="text-3xl mb-3 block">⛔</span>
                            <h4 class="font-black text-gray-900 mb-1">Currently Unavailable</h4>
                            <p class="text-sm font-medium text-gray-500 mb-4">This space is currently rented out or booked by another user.</p>
                            <button disabled class="w-full bg-gray-200 text-gray-400 py-3 rounded-xl font-bold cursor-not-allowed">
                                Not Available
                            </button>
                        </div>

                    @elseif(!auth()->check())
                        <div class="bg-gray-50 border border-gray-100 p-6 rounded-2xl text-center mt-6">
                            <span class="text-3xl mb-3 block">🔒</span>
                            <h4 class="font-black text-gray-900 mb-1">Login Required</h4>
                            <p class="text-sm font-medium text-gray-600 mb-4">Please log in or create an account to request this space.</p>
                            <button @click.prevent="window.dispatchEvent(new CustomEvent('open-login-modal'))" class="w-full bg-gray-900 hover:bg-black text-white py-3 rounded-xl font-bold transition-all shadow-sm">
                                Log In to Continue
                            </button>
                        </div>
                    
                    @elseif(!auth()->user()->is_verified)
                        <div class="bg-orange-50 border border-orange-100 p-6 rounded-2xl text-center mt-6">
                            <span class="text-3xl mb-3 block">🛡️</span>
                            <h4 class="font-black text-gray-900 mb-1">Verification Required</h4>
                            <p class="text-sm font-medium text-gray-600 mb-4">To ensure community safety, you must verify your identity before renting a space.</p>
                            <a href="{{ route('verification.index') }}" class="w-full block bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-bold transition-all shadow-sm text-center">
                                Verify Identity Now
                            </a>
                        </div>
                    
                    @elseif($space->has_active_request)
                        <div class="bg-teal-50 border border-teal-100 p-6 rounded-2xl text-center mt-6">
                            <span class="text-3xl mb-3 block">⏳</span>
                            <h4 class="font-black text-gray-900 mb-1">Request Pending</h4>
                            <p class="text-sm font-medium text-gray-600 mb-4">You already have an active application for this space. Please wait for the host to review it.</p>
                            <a href="{{ route('rents.index') }}" class="w-full block bg-teal-600 hover:bg-teal-700 text-white py-3 rounded-xl font-bold transition-all shadow-sm text-center">
                                Track My Request
                            </a>
                        </div>
                    
                    @else
                        <div class="pt-4 border-t border-gray-100">
                            <a href="{{ route('rents.create', $space->id) }}" class="block w-full bg-orange-500 hover:bg-orange-600 text-white text-center py-4 rounded-2xl font-black transition-all active:scale-95 shadow-lg shadow-orange-500/30">
                                Request to Rent
                            </a>
                            <p class="text-[10px] text-center text-gray-400 font-bold mt-4 uppercase tracking-wider">You won't be charged yet</p>
                        </div>
                    @endif

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