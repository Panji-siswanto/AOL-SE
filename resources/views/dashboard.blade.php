<x-user-layout>
    <div class="bg-[#009485] py-12 px-4">
        <div class="max-w-5xl mx-auto text-center">
            <h1 class="text-4xl font-extrabold text-white mb-8 tracking-tight">Cari Tempat Jualan Terbaikmu</h1>
            
            <div class="relative max-w-3xl mx-auto">
                <input type="text" 
                       placeholder="Cari lokasi atau nama area" 
                       class="w-full pl-12 pr-32 py-5 rounded-2xl border-none shadow-2xl text-lg focus:ring-2 focus:ring-orange-500 outline-none">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button class="absolute right-3 top-1/2 -translate-y-1/2 bg-[#009485] text-white px-8 py-3 rounded-xl font-bold hover:bg-teal-700 transition">
                    Cari
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all overflow-hidden group">
                <div class="relative aspect-[4/3]">
                    <img src="https://images.unsplash.com/photo-1582037928769-181f2644ecb7?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover">
                    <button class="absolute top-4 right-4 bg-white/80 backdrop-blur p-2 rounded-full text-gray-700 hover:text-red-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                    </button>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Lahan Ruko Sudirman</h3>
                    <div class="flex items-center gap-4 text-gray-400 text-xs mb-4">
                        <span class="flex items-center gap-1">📍 Jakarta Barat</span>
                        <span class="flex items-center gap-1">📏 5x15m</span>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div class="flex items-center gap-1 text-sm font-bold">
                            <span class="text-orange-400">★</span> 4.8 <span class="text-gray-400 font-normal">(124)</span>
                        </div>
                        <div class="text-teal-600 font-black text-lg">Rp 500.000/bln</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all overflow-hidden group">
                <div class="relative aspect-[4/3]">
                    <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover">
                    <button class="absolute top-4 right-4 bg-white p-2 rounded-full text-red-500 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Booth Area Tuku</h3>
                    <div class="flex items-center gap-4 text-gray-400 text-xs mb-4">
                        <span class="flex items-center gap-1">📍 Jakarta Pusat</span>
                        <span class="flex items-center gap-1">📏 6x6m</span>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <div class="flex items-center gap-1 text-sm font-bold">
                            <span class="text-orange-400">★</span> 4.9 <span class="text-gray-400 font-normal">(230)</span>
                        </div>
                        <div class="text-teal-600 font-black text-lg">Rp 900.000/bln</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-user-layout>
