<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="{ globalPreview: null }">
        
        {{-- Navigation & Header --}}
        <div class="mb-8">
            <a href="{{ route('owner.spaces.index') }}" class="text-sm font-bold text-gray-400 hover:text-orange-500 transition mb-4 inline-flex items-center gap-2">
                &larr; Back to Management
            </a>
            <div class="mt-2">
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Registration Application</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium">Application ID: REG-{{ $registration->id }} &nbsp;|&nbsp; Submitted: {{ $registration->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>

        {{-- STATUS BANNER (Front and Center for Applications) --}}
        <div class="mb-8 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if($registration->status->code === 'reg_pending')
                    <div class="w-14 h-14 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center text-2xl shadow-sm">⏳</div>
                    <div>
                        <h2 class="text-xl font-black text-orange-900">Awaiting Moderation</h2>
                        <p class="text-sm text-orange-700 font-medium mt-0.5">Your application is currently in the review queue.</p>
                    </div>
                @elseif($registration->status->code === 'reg_rejected')
                    <div class="w-14 h-14 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center text-2xl shadow-sm">❌</div>
                    <div>
                        <h2 class="text-xl font-black text-red-900">Application Rejected</h2>
                        <p class="text-sm text-red-700 font-medium mt-0.5">Please review the admin feedback below and submit a new application.</p>
                    </div>
                @elseif($registration->status->code === 'reg_approved')
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl shadow-sm">✅</div>
                    <div>
                        <h2 class="text-xl font-black text-emerald-900">Approved & Published</h2>
                        <p class="text-sm text-emerald-700 font-medium mt-0.5">This space is live. <a href="#" class="underline font-bold">View Public Listing</a></p>
                    </div>
                @endif
            </div>

            {{-- Admin Feedback (If Rejected) --}}
            @if($registration->status->code === 'reg_rejected' && $registration->logs->count() > 0 && $registration->logs->last()->note)
                <div class="w-full md:w-auto md:max-w-md bg-red-50/50 p-4 rounded-xl border border-red-100">
                    <p class="text-[10px] font-black uppercase tracking-wider text-red-800 mb-1">Admin Note / Reason:</p>
                    <p class="text-sm text-red-900 font-medium">{{ $registration->logs->last()->note }}</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: Application Data --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Submitted Data Overview --}}
                <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-50 pb-4">
                        <span class="w-8 h-8 rounded-xl bg-gray-50 text-gray-600 flex items-center justify-center text-sm">📝</span>
                        Submitted Space Details
                    </h3>
                    
                    <div class="mb-6">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Proposed Name</p>
                        <p class="text-xl font-black text-gray-900">{{ $registration->name }}</p>
                    </div>

                    <div class="mb-6">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Description</p>
                        <div class="prose prose-sm prose-gray max-w-none text-gray-600 font-medium leading-relaxed whitespace-pre-line">
                            {{ $registration->description }}
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-xl">📏</div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Registered Dimensions</p>
                            <p class="text-base font-black text-gray-900">{{ $registration->formatted_size }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Location Coordinates</p>
                        <p class="text-sm font-medium text-gray-600 mb-3">{{ $registration->location->address }}, {{ $registration->location->city }}</p>
                        <div id="readonly-map" class="w-full h-[250px] rounded-2xl border border-gray-200 shadow-inner z-0"></div>
                    </div>
                </div>

                {{-- Draggable Gallery Organizer --}}
                @if($registration->photos->count() > 0)
                    @php $primaryPhoto = $registration->photos->where('is_primary', true)->first() ?? $registration->photos->first(); @endphp
                    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm" x-data="galleryManager('{{ asset('storage/' . $primaryPhoto->file_path) }}', {{ json_encode($primaryPhoto->description ?? '') }})">
                        <div class="flex justify-between items-end mb-6 border-b border-gray-50 pb-4">
                            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                                <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm">📸</span>
                                Photo Gallery Organizer
                            </h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden sm:block">Drag to reorder (Leftmost is Cover)</p>
                        </div>

                        <div class="w-full h-[300px] rounded-2xl overflow-hidden relative mb-4 bg-gray-100 group">
                            <img :src="activeImageUrl" class="w-full h-full object-cover transition duration-300 cursor-pointer" @click="globalPreview = { url: activeImageUrl, type: 'image' }">
                            <div x-show="activeDescription" x-transition.opacity x-cloak class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-gray-900/90 via-gray-900/60 to-transparent p-6 pt-16 pointer-events-none">
                                <p class="text-white font-medium text-sm leading-relaxed" x-text="activeDescription"></p>
                            </div>
                        </div>

                        <div x-ref="sortableList" class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                            @foreach($registration->photos->sortByDesc('is_primary') as $photo)
                                <div data-id="{{ $photo->id }}" 
                                     @click="setActive('{{ asset('storage/' . $photo->file_path) }}', {{ json_encode($photo->description ?? '') }})"
                                     :class="activeImageUrl === '{{ asset('storage/' . $photo->file_path) }}' ? 'ring-4 ring-orange-500 ring-offset-2 opacity-100' : 'opacity-60 hover:opacity-100'"
                                     class="thumbnail-item cursor-move relative w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden transition-all bg-gray-100">
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" class="w-full h-full object-cover pointer-events-none">
                                    <div class="cover-badge absolute top-1 left-1 bg-orange-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm" style="{{ $photo->is_primary ? '' : 'display: none;' }}">COVER</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: Pricing & Documents --}}
            <div class="space-y-8">
                
                {{-- Proposed Rates --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-50 pb-4">
                        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">💰</span>
                        Proposed Rates
                    </h3>
                    @if($registration->prices->count() > 0)
                        <ul class="space-y-3">
                            @foreach($registration->prices as $price)
                                <li class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                                    <span class="text-xs font-bold text-gray-500 uppercase">{{ $price->pricingType->name }}</span>
                                    <span class="text-sm font-black text-teal-700">Rp {{ number_format($price->price, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 italic">No pricing configured.</p>
                    @endif
                </div>

                {{-- Submitted Documents --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-50 pb-4">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">📑</span>
                        Legal Assets
                    </h3>
                    <div class="space-y-3">
                        @foreach($registration->documents as $doc)
                            @php
                                $isPdf = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)) === 'pdf';
                                $fileUrl = asset('storage/' . $doc->file_path);
                            @endphp
                            <div @click="globalPreview = { url: '{{ $fileUrl }}', type: '{{ $isPdf ? 'pdf' : 'image' }}' }" 
                                 class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition">
                                <div class="w-10 h-10 flex-shrink-0 bg-white rounded-lg shadow-sm flex items-center justify-center text-lg">
                                    {{ $isPdf ? '📄' : '🖼️' }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900">{{ $doc->documentType->name ?? 'Document' }}</h4>
                                    <span class="text-[9px] font-black text-blue-600 uppercase mt-0.5 block">View Asset &rarr;</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- Global Preview Modal --}}
        <div x-show="globalPreview !== null" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" @click.self="globalPreview = null" @keydown.escape.window="globalPreview = null">
            <div class="relative w-full max-w-4xl max-h-[95vh] bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col">
                <button type="button" @click="globalPreview = null" class="absolute top-4 right-4 bg-gray-900/80 hover:bg-gray-900 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg backdrop-blur transition z-10">✕</button>
                <div class="p-2 overflow-auto flex-grow flex items-center justify-center bg-gray-900 rounded-3xl">
                    <template x-if="globalPreview?.type === 'image'"><img :src="globalPreview.url" class="max-w-full max-h-[90vh] object-contain rounded-2xl" alt="Preview"></template>
                    <template x-if="globalPreview?.type === 'pdf'"><iframe :src="globalPreview.url" class="w-full h-[85vh] rounded-2xl bg-white border-0"></iframe></template>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('galleryManager', (initialUrl, initialDesc) => ({
                activeImageUrl: initialUrl,
                activeDescription: initialDesc,

                setActive(url, desc) {
                    this.activeImageUrl = url;
                    this.activeDescription = desc;
                },

                init() {
                    let el = this.$refs.sortableList;
                    if (el) {
                        Sortable.create(el, {
                            animation: 200,
                            ghostClass: 'opacity-40', 
                            onEnd: (evt) => {
                                let items = el.querySelectorAll('.thumbnail-item');
                                let newOrder = [];
                                items.forEach((item, index) => {
                                    newOrder.push(item.dataset.id);
                                    let badge = item.querySelector('.cover-badge');
                                    if(badge) badge.style.display = (index === 0) ? 'block' : 'none'; 
                                });
                                fetch(`{{ route('owner.spaces.registrations.photos.reorder', $registration->id) }}`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify({ photo_ids: newOrder })
                                }).catch(error => console.error("Error saving photo order:", error));
                            }
                        });
                    }
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            const lat = {{ $registration->location->latitude ?? -6.28862 }};
            const lng = {{ $registration->location->longitude ?? 106.71789 }};
            const map = L.map('readonly-map', {
                center: [lat, lng], zoom: 16, zoomControl: false, dragging: false, scrollWheelZoom: false, doubleClickZoom: false, boxZoom: false, keyboard: false, touchZoom: false
            });
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            L.marker([lat, lng]).addTo(map);
        });
    </script>
</x-user-layout>