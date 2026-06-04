<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    {{-- Search Hero Section --}}
    <div class="bg-[#009485] py-12 px-4">
        <div class="max-w-5xl mx-auto text-center">
            <h1 class="text-4xl font-extrabold text-white mb-8 tracking-tight">Cari Tempat Jualan Terbaikmu</h1>
            
            <form action="{{ route('dashboard') }}" method="GET" class="relative max-w-3xl mx-auto">
                {{-- Preserve active filters when searching --}}
                @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if(request('min_price')) <input type="hidden" name="min_price" value="{{ request('min_price') }}"> @endif
                @if(request('max_price')) <input type="hidden" name="max_price" value="{{ request('max_price') }}"> @endif
                @if(request('min_area')) <input type="hidden" name="min_area" value="{{ request('min_area') }}"> @endif
                @if(request('max_area')) <input type="hidden" name="max_area" value="{{ request('max_area') }}"> @endif

                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari lokasi atau nama area (ex: Jakarta Barat, Tuku...)" 
                       class="w-full pl-12 pr-32 py-5 rounded-2xl border-none shadow-2xl text-lg focus:ring-2 focus:ring-orange-500 outline-none text-gray-900">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 bg-[#009485] text-white px-8 py-3 rounded-xl font-bold hover:bg-teal-700 transition">
                    Cari
                </button>
            </form>
        </div>
    </div>

    {{-- Discovery Area --}}
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        {{-- FILTER & SORT PANEL --}}
     {{-- FILTER & SORT PANEL --}}
        {{-- Keep panel open if any filters are active in the URL --}}
        <div x-data="{ showFilters: {{ request()->anyFilled(['min_price', 'max_price', 'min_area', 'max_area']) ? 'true' : 'false' }} }" class="mb-8 relative z-20">
            <form action="{{ route('dashboard') }}" method="GET">
                {{-- Preserve search keyword when filtering --}}
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif

                {{-- Toolbar --}}
                <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 px-6 rounded-2xl md:rounded-[2rem] border border-gray-100 shadow-sm gap-4 transition-all">
                    <div class="text-sm font-bold text-gray-500">
                        Menampilkan <span class="text-gray-900">{{ $spaces->total() }}</span> lahan tersedia
                    </div>
                    
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        {{-- Sort Dropdown --}}
                        <div class="relative w-full md:w-auto">
                            <select name="sort" onchange="this.form.submit()" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-teal-500 focus:border-teal-500 block p-2.5 px-4 font-bold cursor-pointer outline-none hover:bg-gray-50 transition shadow-sm w-full appearance-none pr-10">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga: Terendah</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga: Tertinggi</option>
                                <option value="area_desc" {{ request('sort') == 'area_desc' ? 'selected' : '' }}>Luas: Terbesar</option>
                                <option value="area_asc" {{ request('sort') == 'area_asc' ? 'selected' : '' }}>Luas: Terkecil</option>
                            </select>
                            {{-- Custom dropdown arrow to match the rounded aesthetic --}}
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        {{-- Toggle Filters Button --}}
                        <button type="button" @click="showFilters = !showFilters" 
                                :class="showFilters ? 'bg-teal-50 border-teal-200 text-teal-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                                class="flex items-center justify-center gap-2 border px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-sm w-full md:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                            Filter Lahan
                        </button>
                    </div>
                </div>

                {{-- Collapsible Filter UI (Matches your screenshot exactly) --}}
                <div x-show="showFilters" x-collapse x-cloak class="mt-4 bg-white p-6 md:p-8 rounded-[2rem] border border-gray-100 shadow-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        
                        {{-- Price Filter --}}
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-4">Rentang Harga (Rp)</label>
                            <div class="flex items-center gap-3">
                                
                                {{-- Min Price --}}
                                <div class="relative w-full" x-data="{ raw: '{{ request('min_price') }}', formatted: '{{ request('min_price') ? number_format(request('min_price'), 0, ',', '.') : '' }}' }">
                                    <span class="absolute left-4 top-3 text-gray-400 text-sm font-bold">Min</span>
                                    <input type="text" x-model="formatted" @input="raw = $event.target.value.replace(/\D/g, ''); formatted = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''" placeholder="0" class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:border-teal-500 focus:bg-white transition outline-none shadow-inner">
                                    <input type="hidden" name="min_price" :value="raw">
                                </div>
                                
                                <span class="text-gray-300 font-bold">-</span>
                                
                                {{-- Max Price --}}
                                <div class="relative w-full" x-data="{ raw: '{{ request('max_price') }}', formatted: '{{ request('max_price') ? number_format(request('max_price'), 0, ',', '.') : '' }}' }">
                                    <span class="absolute left-4 top-3 text-gray-400 text-sm font-bold">Max</span>
                                    <input type="text" x-model="formatted" @input="raw = $event.target.value.replace(/\D/g, ''); formatted = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''" placeholder="Tak Terbatas" class="w-full pl-14 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:border-teal-500 focus:bg-white transition outline-none shadow-inner">
                                    <input type="hidden" name="max_price" :value="raw">
                                </div>
                            </div>
                        </div>

                        {{-- Area Filter --}}
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-4">Luas Area (m²)</label>
                            <div class="flex items-center gap-3">
                                <div class="relative w-full">
                                    <input type="number" name="min_area" value="{{ request('min_area') }}" placeholder="Min m²" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:border-teal-500 focus:bg-white transition outline-none shadow-inner">
                                </div>
                                <span class="text-gray-300 font-bold">-</span>
                                <div class="relative w-full">
                                    <input type="number" name="max_area" value="{{ request('max_area') }}" placeholder="Max m²" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:border-teal-500 focus:bg-white transition outline-none shadow-inner">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="mt-8 pt-6 border-t border-gray-50 flex items-center justify-end gap-6">
                        {{-- Keep the search string when resetting filters --}}
                        <a href="{{ route('dashboard', request('search') ? ['search' => request('search')] : []) }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition">Reset Filter</a>
                        <button type="submit" class="bg-[#009485] hover:bg-teal-700 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-md shadow-teal-500/20 transition active:scale-95">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>
        {{-- Grid Section --}}
        @if($spaces->isEmpty())
            <div class="text-center py-20 bg-white rounded-[2rem] border border-gray-100 shadow-sm">
                <span class="text-6xl mb-4 block">🏜️</span>
                <h3 class="text-xl font-bold text-gray-900">Tidak ada lahan yang ditemukan</h3>
                <p class="text-gray-500 mt-2">Coba sesuaikan filter atau gunakan kata kunci lain.</p>
                <a href="{{ route('dashboard') }}" class="inline-block mt-4 text-teal-600 font-bold hover:underline">Clear All Filters & Search</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($spaces as $space)
                    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all overflow-hidden group flex flex-col relative">
                        
                        {{-- Bookmark Heart Button --}}
                        <button class="absolute top-4 right-4 z-10 bg-white/80 backdrop-blur p-2 rounded-full text-gray-400 hover:text-red-500 hover:bg-white shadow-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                        </button>

                        <a href="{{ route('spaces.show', $space->id) }}" class="flex-grow flex flex-col">
                            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                                <img src="{{ $space->cover_photo_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </div>
                            
                            <div class="p-6 flex-grow flex flex-col">
                                <h3 class="text-xl font-bold text-gray-900 mb-1 group-hover:text-teal-600 transition truncate" title="{{ $space->name }}">
                                    {{ $space->name }}
                                </h3>
                                
                                <div class="flex items-center gap-4 text-gray-400 text-xs mb-4">
                                    <span class="flex items-center gap-1 truncate max-w-[120px]">📍 {{ $space->location->city }}</span>
                                    <span class="flex items-center gap-1">📏 {{ $space->length && $space->width ? $space->length.'x'.$space->width.'m' : $space->area.'m²' }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center pt-4 border-t border-gray-50 mt-auto">
                                    <div class="flex items-center gap-1 text-sm font-bold">
                                        <span class="text-orange-400">★</span> 4.8 <span class="text-gray-400 font-normal">(124)</span>
                                    </div>
                                    <div class="text-teal-600 font-black text-lg">
                                        Rp {{ number_format($space->price, 0, ',', '.') }}<span class="text-xs font-normal text-gray-500">/start</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div class="mt-12">
                {{ $spaces->links() }}
            </div>
        @endif
    </div>

    {{-- Make sure Alpine Collapse plugin is loaded for the smooth animation --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
</x-user-layout>