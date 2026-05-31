<x-guest-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-4xl mx-auto bg-white p-6 sm:p-8 rounded-[2rem] border border-gray-100 shadow-xl shadow-gray-100/50">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Buat Akun Baru</h2>
            <p class="text-xs text-gray-500 mt-2">Daftar, verifikasi identitas Anda, dan mulai eksplorasi Lapak.in</p>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-800 text-xs font-bold flex items-center gap-3 shadow-sm">
                <span>❌</span> {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="register-form" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus 
                           class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl px-4 py-2.5 text-sm outline-none transition font-medium text-gray-900">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required 
                           class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl px-4 py-2.5 text-sm outline-none transition font-medium text-gray-900">
                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>

                <div x-data="countryCodes()">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nomor Telepon</label>
                    
                    <div @click.away="open = false" class="relative flex rounded-xl overflow-visible border border-gray-200 focus-within:ring-2 focus-within:ring-orange-200 focus-within:border-orange-500 transition bg-gray-50">
                        
                        <input type="hidden" name="phone_code" :value="selectedCountry.code">
                        
                        <button type="button" @click="open = !open" 
                                class="flex items-center justify-between gap-1.5 bg-gray-50 border-r border-gray-200 text-gray-700 text-sm font-bold py-2.5 px-3 hover:bg-gray-100 transition shrink-0 w-28 focus:outline-none rounded-l-xl">
                            <span class="flex items-center gap-1.5">
                                <span x-text="selectedCountry.flag" class="text-base"></span>
                                <span x-text="selectedCountry.code"></span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" x-transition x-cloak
                             class="absolute z-50 top-full left-0 mt-1.5 w-64 bg-white border border-gray-100 rounded-xl shadow-2xl overflow-hidden">
                             
                            <div class="p-2 border-b border-gray-50 bg-gray-50/50">
                                <input type="text" x-model="search" placeholder="Cari..." 
                                       class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-200 font-medium">
                            </div>

                            <ul class="max-h-48 overflow-y-auto p-1 scrollbar-thin scrollbar-thumb-gray-200">
                                <template x-for="country in filteredCountries" :key="country.code">
                                    <li @click="selectCountry(country)" 
                                        class="cursor-pointer px-3 py-2.5 text-xs hover:bg-orange-50 hover:text-orange-600 rounded-lg flex items-center gap-2.5 transition-colors">
                                        <span x-text="country.flag" class="text-base"></span>
                                        <span x-text="country.name" class="font-medium flex-grow truncate"></span>
                                        <span x-text="country.code" class="text-gray-400 font-bold shrink-0"></span>
                                    </li>
                                </template>
                                <li x-show="filteredCountries.length === 0" class="px-3 py-4 text-center text-xs text-gray-400 font-medium">
                                    Negara tidak ditemukan
                                </li>
                            </ul>
                        </div>

                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="8123456789" required
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               class="w-full bg-gray-50 border-none px-3 py-2.5 text-sm outline-none focus:ring-0 font-medium text-gray-900 rounded-r-xl">
                    </div>
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required 
                           class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl px-4 py-2.5 text-sm outline-none transition font-medium text-gray-900">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div x-data="{ show: false }">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl px-4 py-2.5 text-sm outline-none transition font-medium text-gray-900">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div x-data="{ show: false }">
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" required 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl px-4 py-2.5 text-sm outline-none transition font-medium text-gray-900">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-5 border-t border-gray-100">
                <h3 class="text-[11px] font-black text-gray-900 mb-3 uppercase tracking-wider">Verifikasi Identitas</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="relative bg-gray-50 p-3 rounded-2xl border-2 border-dashed border-gray-200 hover:border-orange-500 transition-all group overflow-hidden">
                        <label class="cursor-pointer block">
                            <div id="ktp-placeholder" class="text-center py-4">
                                <div class="w-10 h-10 bg-white text-orange-500 rounded-xl flex items-center justify-center mx-auto mb-2 shadow-sm group-hover:scale-110 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <span class="text-xs font-bold text-gray-900 block">Foto KTP</span>
                                <p class="text-[9px] text-gray-400 mt-0.5">Format: JPG/PNG, Maks: 2MB</p>
                            </div>
                            <img id="ktp-preview" class="hidden w-full h-24 object-cover rounded-xl mb-1">
                            <input type="file" name="ktp" id="ktp-input" class="hidden" accept="image/jpeg,image/png,image/jpg" required>
                        </label>
                    </div>

                    <div class="relative bg-gray-50 p-3 rounded-2xl border-2 border-dashed border-gray-200 hover:border-orange-500 transition-all group overflow-hidden">
                        <label class="cursor-pointer block">
                            <div id="selfie-placeholder" class="text-center py-4">
                                <div class="w-10 h-10 bg-white text-orange-500 rounded-xl flex items-center justify-center mx-auto mb-2 shadow-sm group-hover:scale-110 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <span class="text-xs font-bold text-gray-900 block">Selfie + KTP</span>
                                <p class="text-[9px] text-gray-400 mt-0.5">Pastikan wajah & KTP jelas</p>
                            </div>
                            <img id="selfie-preview" class="hidden w-full h-24 object-cover rounded-xl mb-1">
                            <input type="file" name="selfie_ktp" id="selfie-input" class="hidden" accept="image/jpeg,image/png,image/jpg" required>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center justify-center pt-2">
                <button type="submit" id="submit-btn" class="w-full md:w-auto bg-orange-500 text-white px-10 py-3.5 rounded-xl font-black text-sm shadow-xl shadow-orange-500/30 hover:bg-orange-600 active:scale-95 transition-all">
                    Daftar & Kirim Verifikasi
                </button>
                <a href="{{ route('login') }}" class="mt-4 text-[11px] font-bold text-gray-400 hover:text-orange-500 transition underline">
                    Sudah punya akun? Masuk sekarang
                </a>
            </div>
        </form>
    </div>

  <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('countryCodes', () => ({
                open: false,
                search: '',
                countries: [],
                filteredCountries: [],
                selectedCountry: { name: 'Indonesia', code: '+62', flag: '🇮🇩' }, // Safe default
                
                init() {
                    fetch('https://restcountries.com/v3.1/all?fields=name,idd,flag')
                        .then(res => res.json())
                        .then(data => {
                            let parsed = data
                                .filter(c => c.idd && c.idd.root)
                                .map(c => {
                                    let root = c.idd.root;
                                    // Safely grab the first suffix if it exists
                                    let suffix = (c.idd.suffixes && c.idd.suffixes.length === 1) ? c.idd.suffixes[0] : '';
                                    let combined = root + suffix;
                                    
                                    // THE SMART FILTER: Global country codes are a maximum of 3 digits.
                                    // Including the '+', valid codes max out at 4 characters.
                                    // This perfectly strips regional area codes like +1201 (US) down to +1, and +35818 (Aland) down to +358.
                                    let finalCode = combined.length <= 4 ? combined : root;

                                    return {
                                        name: c.name.common,
                                        code: finalCode,
                                        flag: c.flag
                                    };
                                });

                            // Deduplicate by Country NAME (Allows US and Canada to both safely exist with +1)
                            let seenNames = new Set();
                            let uniqueCountries = [];
                            
                            parsed.forEach(c => {
                                if (!seenNames.has(c.name)) {
                                    seenNames.add(c.name);
                                    uniqueCountries.push(c);
                                }
                            });

                            // Alphabetical sorting
                            uniqueCountries.sort((a, b) => a.name.localeCompare(b.name));

                            // Force Indonesia to the top of the array
                            const idIndex = uniqueCountries.findIndex(c => c.name === 'Indonesia');
                            if (idIndex > -1) {
                                const id = uniqueCountries.splice(idIndex, 1)[0];
                                uniqueCountries.unshift(id);
                                this.selectedCountry = id;
                            }
                            
                            this.countries = uniqueCountries;
                            this.filteredCountries = uniqueCountries;
                        })
                        .catch(err => console.error('Failed to load API country data', err));

                    // Watch the search input to instantly filter the list
                    this.$watch('search', value => {
                        if (value === '') {
                            this.filteredCountries = this.countries;
                        } else {
                            const lowerCaseSearch = value.toLowerCase();
                            this.filteredCountries = this.countries.filter(c => 
                                c.name.toLowerCase().includes(lowerCaseSearch) || 
                                c.code.includes(lowerCaseSearch)
                            );
                        }
                    });
                },

                // Click handler for dropdown items
                selectCountry(country) {
                    this.selectedCountry = country;
                    this.open = false;
                    this.search = '';
                }
            }));
        });

        // Image Preview and Submission Logic
        function setupPreview(inputId, previewId, placeholderId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById(placeholderId);

            if (!input || !preview || !placeholder) return;

            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal 2MB.');
                        this.value = '';
                        preview.classList.add('hidden');
                        placeholder.classList.remove('hidden');
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupPreview('ktp-input', 'ktp-preview', 'ktp-placeholder');
            setupPreview('selfie-input', 'selfie-preview', 'selfie-placeholder');

            const form = document.getElementById('register-form');
            const submitBtn = document.getElementById('submit-btn');

            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    submitBtn.innerHTML = 'Memproses...';
                });
            }
        });
    </script>
</x-guest-layout>