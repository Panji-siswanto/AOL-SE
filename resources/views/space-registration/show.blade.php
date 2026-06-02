<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="{ globalPreview: null }">
        
        {{-- Top Navigation & Header --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <a href="{{ route('space-registrations.index') }}" class="text-sm font-bold text-gray-400 hover:text-orange-500 transition mb-4 inline-flex items-center gap-2">
                    &larr; Back to My Listings
                </a>
                <div class="flex items-center gap-4 mt-2">
                    <h1 class="text-4xl font-black text-gray-900 tracking-tight">{{ $registration->name }}</h1>
                    
                    @php
                        $badgeColor = match($registration->status->code) {
                            'reg_pending' => 'bg-orange-100 text-orange-700 border-orange-200',
                            'reg_approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'reg_rejected' => 'bg-red-100 text-red-700 border-red-200',
                            default => 'bg-gray-100 text-gray-700 border-gray-200'
                        };
                    @endphp
                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border {{ $badgeColor }}">
                        {{ $registration->status->name }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-2 flex items-center gap-2 font-medium">
                    <span>📍 {{ $registration->location->city }}, {{ $registration->location->province }}</span>
                    <span class="text-gray-300">|</span>
                    <span>Submitted on {{ $registration->created_at->format('d M Y, H:i') }}</span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: Photos & Main Details --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Draggable Alpine.js Gallery --}}
                @if($registration->photos->count() > 0)
                    @php
                        // Find the primary photo, fallback to the first photo
                        $primaryPhoto = $registration->photos->where('is_primary', true)->first() ?? $registration->photos->first();
                    @endphp
                    
                    <div class="bg-white p-4 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40" 
                         x-data="galleryManager('{{ asset('storage/' . $primaryPhoto->file_path) }}', {{ json_encode($primaryPhoto->description ?? '') }})">
                        
                        {{-- Main Image Showcase with Description Overlay --}}
                        <div class="w-full h-[400px] rounded-3xl overflow-hidden relative mb-4 bg-gray-100 group">
                            <img :src="activeImageUrl" class="w-full h-full object-cover transition duration-300 cursor-pointer" @click="globalPreview = { url: activeImageUrl, type: 'image' }">
                            
                            {{-- Image Description Overlay (Only shows if description exists) --}}
                            <div x-show="activeDescription" x-transition.opacity x-cloak
                                 class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-gray-900/90 via-gray-900/60 to-transparent p-6 pt-16 pointer-events-none">
                                <p class="text-white font-medium text-sm leading-relaxed" x-text="activeDescription"></p>
                            </div>
                        </div>

                        {{-- Draggable Thumbnails --}}
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3 px-2">Drag to reorder (Leftmost is Cover)</p>
                        
                        <div x-ref="sortableList" class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                            {{-- We sort by is_primary descending to ensure the cover is visually first on load --}}
                            @foreach($registration->photos->sortByDesc('is_primary') as $photo)
                                <div data-id="{{ $photo->id }}" 
                                     @click="setActive('{{ asset('storage/' . $photo->file_path) }}', {{ json_encode($photo->description ?? '') }})"
                                     :class="activeImageUrl === '{{ asset('storage/' . $photo->file_path) }}' ? 'ring-4 ring-orange-500 ring-offset-2 opacity-100' : 'opacity-60 hover:opacity-100'"
                                     class="thumbnail-item cursor-move relative w-24 h-24 flex-shrink-0 rounded-2xl overflow-hidden transition-all bg-gray-100">
                                    
                                    <img src="{{ asset('storage/' . $photo->file_path) }}" class="w-full h-full object-cover pointer-events-none">
                                    
                                    {{-- Dynamic Cover Badge --}}
                                    <div class="cover-badge absolute top-1 left-1 bg-orange-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm" 
                                         style="{{ $photo->is_primary ? '' : 'display: none;' }}">
                                         COVER
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Space Details & Location --}}
                <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm">📝</span>
                        Space Description
                    </h3>
                    
                    <div class="prose prose-sm prose-gray max-w-none text-gray-600 font-medium leading-relaxed whitespace-pre-line mb-8">
                        {{ $registration->description }}
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-2xl">📏</div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Dimensions / Size</p>
                            <p class="text-lg font-black text-gray-900">{{ $registration->size }}</p>
                        </div>
                    </div>

                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2 pt-6 border-t border-gray-100">
                        <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm">📍</span>
                        Location Map
                    </h3>
                    <p class="text-sm font-medium text-gray-600 mb-4">{{ $registration->location->address }}</p>
                    
                    {{-- Read-Only Map --}}
                    <div id="readonly-map" class="w-full h-[300px] rounded-3xl border border-gray-200 shadow-inner z-0"></div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Pricing & Documents --}}
            <div class="space-y-8">
                
                {{-- Dynamic Pricing Rates --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                    <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">💰</span>
                        Rental Rates
                    </h3>

                    @if($registration->prices->count() > 0)
                        <ul class="space-y-4">
                            @foreach($registration->prices as $price)
                                <li class="flex justify-between items-center p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-emerald-200 transition">
                                    <span class="text-sm font-bold text-gray-600">{{ $price->pricingType->name }}</span>
                                    <span class="text-base font-black text-teal-700">Rp {{ number_format($price->price, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 italic">No pricing configured.</p>
                    @endif
                </div>

                {{-- Legal Documents --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                    <h3 class="text-lg font-black text-gray-900 mb-2 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">📑</span>
                        Legal Assets
                    </h3>
                    <p class="text-xs text-gray-400 mb-6 font-medium">Click on a document to preview it.</p>

                    <div class="space-y-4">
                        @foreach($registration->documents as $doc)
                            @php
                                $extension = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                                $isPdf = strtolower($extension) === 'pdf';
                                $fileUrl = asset('storage/' . $doc->file_path);
                            @endphp

                            <div @click="globalPreview = { url: '{{ $fileUrl }}', type: '{{ $isPdf ? 'pdf' : 'image' }}' }" 
                                 class="group flex items-start gap-4 p-4 rounded-2xl border-2 border-gray-100 hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition">
                                <div class="w-12 h-12 flex-shrink-0 bg-white rounded-xl shadow-sm flex items-center justify-center text-2xl group-hover:scale-110 transition">
                                    {{ $isPdf ? '📄' : '🖼️' }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ $doc->documentType->name ?? 'Document' }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $doc->description }}</p>
                                    <span class="inline-block mt-2 text-[10px] font-black text-blue-600 uppercase tracking-wider bg-white px-2 py-1 rounded-lg border border-blue-100 shadow-sm">
                                        View File &rarr;
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

              {{-- Action / Status Box --}}
                @if($registration->status->code === 'reg_pending')
                    <div class="bg-orange-50 p-6 rounded-[2rem] border border-orange-100 text-center">
                        <div class="w-12 h-12 bg-white text-orange-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-3 shadow-sm">⏳</div>
                        <h4 class="font-black text-orange-900 mb-1">Awaiting Review</h4>
                        <p class="text-xs text-orange-700 font-medium">Our moderation team is currently reviewing your application. You will be notified once it is approved.</p>
                    </div>
                @elseif($registration->status->code === 'reg_rejected')
                    <div class="bg-red-50 p-6 rounded-[2rem] border border-red-100 text-center">
                        <div class="w-12 h-12 bg-white text-red-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-3 shadow-sm">❌</div>
                        <h4 class="font-black text-red-900 mb-1">Application Rejected</h4>
                        <p class="text-xs text-red-700 font-medium mb-4">Your application was not approved by our moderation team.</p>
                        
                        @if($registration->logs->count() > 0 && $registration->logs->last()->note)
                            <div class="bg-white/80 p-4 rounded-xl text-left border border-red-200 shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-wider text-red-800 mb-1">Admin Feedback:</p>
                                <p class="text-sm text-red-900 font-medium">{{ $registration->logs->last()->note }}</p>
                            </div>
                        @endif
                    </div>
                @elseif($registration->status->code === 'reg_approved')
                     <div class="bg-emerald-50 p-6 rounded-[2rem] border border-emerald-100 text-center">
                        <div class="w-12 h-12 bg-white text-emerald-500 rounded-2xl flex items-center justify-center text-xl mx-auto mb-3 shadow-sm">✅</div>
                        <h4 class="font-black text-emerald-900 mb-1">Live on Marketplace</h4>
                        <p class="text-xs text-emerald-700 font-medium">This space is officially approved and visible to renters.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Global Preview Modal --}}
        <div x-show="globalPreview !== null" x-cloak
             x-transition.opacity
             class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" 
             @click.self="globalPreview = null" 
             @keydown.escape.window="globalPreview = null">
            <div class="relative w-full max-w-4xl max-h-[95vh] bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col">
                <button type="button" @click="globalPreview = null" class="absolute top-4 right-4 bg-gray-900/80 hover:bg-gray-900 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg backdrop-blur transition z-10">✕</button>
                
                <div class="p-2 overflow-auto flex-grow flex items-center justify-center bg-gray-900 rounded-3xl">
                    <template x-if="globalPreview?.type === 'image'">
                        <img :src="globalPreview.url" class="max-w-full max-h-[90vh] object-contain rounded-2xl" alt="Full Preview">
                    </template>
                    <template x-if="globalPreview?.type === 'pdf'">
                        <iframe :src="globalPreview.url" class="w-full h-[85vh] rounded-2xl bg-white border-0"></iframe>
                    </template>
                </div>
            </div>
        </div>

    </div>

    {{-- Map & Sortable JS Dependencies --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            
            // Alpine Component to handle the Draggable Gallery & Main Image View
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
                            ghostClass: 'opacity-40', // Makes the dragged item semi-transparent
                            onEnd: (evt) => {
                                let items = el.querySelectorAll('.thumbnail-item');
                                let newOrder = [];
                                
                                // Update visually and capture new order
                                items.forEach((item, index) => {
                                    newOrder.push(item.dataset.id);
                                    let badge = item.querySelector('.cover-badge');
                                    if(badge) badge.style.display = (index === 0) ? 'block' : 'none'; 
                                });

                                // Silently sync the new order to the database via API fetch
                                fetch(`{{ route('space-registrations.photos.reorder', $registration->id) }}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ photo_ids: newOrder })
                                }).catch(error => console.error("Error saving photo order:", error));
                            }
                        });
                    }
                }
            }));
        });

        // Initialize Read-Only Leaflet Map
        document.addEventListener('DOMContentLoaded', function() {
            const lat = {{ $registration->location->latitude ?? -6.28862 }};
            const lng = {{ $registration->location->longitude ?? 106.71789 }};

            const map = L.map('readonly-map', {
                center: [lat, lng],
                zoom: 16,
                zoomControl: false,
                dragging: false,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                boxZoom: false,
                keyboard: false,
                touchZoom: false
            });

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map);
        });
    </script>
</x-user-layout>