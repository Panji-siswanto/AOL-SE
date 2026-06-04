<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>

    {{-- 1. Initialize Alpine State for the Total Count & Listen for removals --}}
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" 
         x-data="{ totalBookmarks: {{ $spaces->total() }} }"
         @bookmark-removed.window="totalBookmarks > 0 ? totalBookmarks-- : 0">
        
        {{-- Page Header --}}
        <div class="mb-10 border-b border-gray-100 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <a href="{{ route('dashboard') }}" class="text-sm font-bold text-gray-400 hover:text-teal-600 transition flex items-center gap-2 mb-4">
                    &larr; Lanjut Cari Lahan
                </a>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                    <span class="text-teal-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8"><path fill-rule="evenodd" d="M6.5 3A1.5 1.5 0 0 0 5 4.5v17.596c0 .514.61.776 1.01.442L12 18.75l4.49 3.788c.4.334 1.01.072 1.01-.442V4.5A1.5 1.5 0 0 0 16 3H6.5Z" clip-rule="evenodd" /></svg>
                    </span> 
                    Lahan Tersimpan
                </h1>
                <p class="text-gray-500 mt-2 font-medium">Koleksi lapak dan area potensial yang sudah kamu tandai.</p>
            </div>
            
            {{-- 2. Bind the Total Count to the UI --}}
            <div x-show="totalBookmarks > 0" class="text-sm font-bold text-gray-500 bg-white border border-gray-200 px-4 py-2 rounded-xl shadow-sm">
                Total: <span class="text-gray-900" x-text="totalBookmarks">{{ $spaces->total() }}</span> Lahan
            </div>
        </div>

        {{-- Initial Empty State (from Database) --}}
        @if($spaces->isEmpty())
            <div class="text-center py-24 bg-white rounded-[2rem] border border-gray-100 shadow-sm flex flex-col items-center justify-center">
                <div class="w-24 h-24 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 22.096c0 .514-.61.776-1.01.442L12 18.75l-4.49 3.788c-.4.334-1.01.072-1.01-.442V4.5A1.5 1.5 0 0 1 8 3h8a1.5 1.5 0 0 1 1.5 1.5v17.596Z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">Belum ada lahan yang disimpan</h3>
                <p class="text-gray-500 mb-8 max-w-md">Tandai lahan yang kamu suka saat menjelajah untuk membandingkannya nanti di sini.</p>
                <a href="{{ route('dashboard') }}" class="bg-teal-600 hover:bg-teal-700 text-white px-8 py-3.5 rounded-xl text-sm font-bold shadow-md shadow-teal-500/20 transition active:scale-95">
                    Mulai Eksplorasi Lahan
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($spaces as $space)
                    <div x-data="{ isVisible: true }" x-show="isVisible" x-transition.duration.500ms class="bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all overflow-hidden group flex flex-col relative">
                        
                        {{-- Interactive Bookmark Button --}}
                        <button @click.prevent="toggleBookmark"
                                x-data="{
                                    bookmarked: {{ in_array($space->id, $bookmarkedSpaceIds) ? 'true' : 'false' }},
                                    loading: false,
                                    toggleBookmark() {
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
                                            // 3. Dispatch the event and hide the card visually
                                            if(!this.bookmarked) {
                                                $dispatch('bookmark-removed');
                                                setTimeout(() => { isVisible = false; }, 300);
                                            }
                                        }).catch(() => this.loading = false);
                                    }
                                }"
                                :class="bookmarked ? 'text-teal-600 bg-teal-50 border border-teal-100' : 'text-gray-400 bg-white/80 hover:text-teal-600 hover:bg-white'"
                                class="absolute top-4 right-4 z-10 backdrop-blur p-2.5 rounded-full shadow-sm transition duration-300">
                            
                            <svg x-show="!bookmarked" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 22.096c0 .514-.61.776-1.01.442L12 18.75l-4.49 3.788c-.4.334-1.01.072-1.01-.442V4.5A1.5 1.5 0 0 1 8 3h8a1.5 1.5 0 0 1 1.5 1.5v17.596Z" />
                            </svg>
                            <svg x-show="bookmarked" style="display: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M6.5 3A1.5 1.5 0 0 0 5 4.5v17.596c0 .514.61.776 1.01.442L12 18.75l4.49 3.788c.4.334 1.01.072 1.01-.442V4.5A1.5 1.5 0 0 0 16 3H6.5Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <a href="{{ route('spaces.show', $space->id) }}" class="flex-grow flex flex-col">
                            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                                @php
                                    $photos = $space->photos->count() > 0 ? $space->photos : $space->registration->photos; 
                                    $coverUrl = $photos->count() > 0 ? asset('storage/' . ($photos->where('is_primary', true)->first()->file_path ?? $photos->first()->file_path)) : '';
                                @endphp
                                <img src="{{ $coverUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
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

            <div class="mt-12">
                {{ $spaces->links() }}
            </div>
            
            <div x-show="totalBookmarks === 0" x-cloak class="text-center py-24 bg-white rounded-[2rem] border border-gray-100 shadow-sm flex flex-col items-center justify-center mt-8">
                <div class="w-24 h-24 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 mb-2">Semua lahan telah dihapus</h3>
                <p class="text-gray-500 mb-8 max-w-md">Lanjutkan pencarian untuk menemukan lapak terbaik lainnya.</p>
                <a href="{{ route('dashboard') }}" class="bg-teal-600 hover:bg-teal-700 text-white px-8 py-3.5 rounded-xl text-sm font-bold shadow-md shadow-teal-500/20 transition active:scale-95">
                    Kembali Eksplorasi
                </a>
            </div>
        @endif
    </div>
</x-user-layout>