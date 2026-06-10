<x-user-layout>
    <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-10">
            <span class="text-xs font-black bg-orange-50 text-orange-600 px-3 py-1 rounded-full uppercase tracking-wider border border-orange-100">
                Lapak.in Host Portal
            </span>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight mt-3">List Your Space</h1>
            <p class="text-sm text-gray-500 mt-2 max-w-xl mx-auto">
                Register your unused spot, booth, or ruko legally. Once approved by our moderation team, your space will go live on the public discovery catalog.
            </p>
        </div>

        @if(session('error'))
            <div class="mb-8 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>❌</span> {{ session('error') }}
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

            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm font-bold">1</span>
                    Basic Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Listing Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               placeholder="e.g., Lapak Strategis Depan Kampus / Ruko Minimalis Booth Spot" 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Description *</label>
                        <textarea name="description" rows="4" required 
                                  placeholder="Provide key details about foot traffic, nearby landmarks, available electrical outlets, or custom operational requirements..." 
                                  class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Dimensions / Size *</label>
                        <input type="text" name="size" value="{{ old('size') }}" required 
                               placeholder="e.g., 2x2 Meter / 3x4 m" 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Monthly Price (Rp) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-sm font-bold text-gray-400">Rp</span>
                            <input type="number" name="price" value="{{ old('price') }}" required min="0" step="1000"
                                   placeholder="1500000" 
                                   class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl pl-12 pr-4 py-3.5 text-sm outline-none transition font-black text-gray-900">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm font-bold">2</span>
                    Location Mapping
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">City / Municipality *</label>
                        <input type="text" name="city" value="{{ old('city') }}" required 
                               placeholder="e.g., South Tangerang" 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Province *</label>
                        <input type="text" name="province" value="{{ old('province') }}" required 
                               placeholder="e.g., Banten" 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Full Address *</label>
                        <input type="text" name="address" value="{{ old('address') }}" required     
                               placeholder="Street Name, Building/Lot No., RT/RW, District..." 
                               class="w-full bg-gray-50 border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-2xl px-4 py-3.5 text-sm outline-none transition font-medium text-gray-900">

                            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 shadow-sm min-h-[240px] flex items-center justify-center mt-6">
                                <div class="text-center text-slate-400">
                                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-2xl">🗺️</div>
                                    <p class="text-sm font-medium">Map Placeholder</p>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                <h3 class="text-lg font-black text-gray-900 mb-2 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm font-bold">3</span>
                    Legal Verification Assets
                </h3>
                <p class="text-xs text-gray-400 mb-6 pl-10">We verify the legal authorization of all listing providers to keep the community safe.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Surat Kepemilikan Lahan / Sertifikat *
                        </label>
                        <div class="relative bg-gray-50 p-6 rounded-3xl border-2 border-dashed border-gray-200 text-center hover:border-orange-500 transition group overflow-hidden">
                            <label class="cursor-pointer block">
                                <div id="tanah-placeholder">
                                    <div class="w-12 h-12 bg-white text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-sm group-hover:scale-110 transition">
                                        📄
                                    </div>
                                    <span class="text-xs font-bold text-gray-900 block">Upload Land Certificate</span>
                                    <span class="text-[10px] text-gray-400 block mt-1">PDF, JPG or PNG (Max 5MB)</span>
                                </div>
                                <div id="tanah-preview-container" class="hidden flex items-center justify-center gap-2 text-xs font-bold text-teal-600 bg-teal-50 py-2 rounded-xl border border-teal-100">
                                    <span>✓ Selected File Bound</span>
                                </div>
                                <input type="file" name="surat_tanah" id="tanah-input" class="hidden" accept=".pdf,image/*" required>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Surat Perjanjian Sewa / Izin Usaha (Optional)
                        </label>
                        <div class="relative bg-gray-50 p-6 rounded-3xl border-2 border-dashed border-gray-200 text-center hover:border-orange-500 transition group overflow-hidden">
                            <label class="cursor-pointer block">
                                <div id="izin-placeholder">
                                    <div class="w-12 h-12 bg-white text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-sm group-hover:scale-110 transition">
                                        🤝
                                    </div>
                                    <span class="text-xs font-bold text-gray-900 block">Upload Secondary Agreements</span>
                                    <span class="text-[10px] text-gray-400 block mt-1">Supporting Master Rental Contracts</span>
                                </div>
                                <div id="izin-preview-container" class="hidden flex items-center justify-center gap-2 text-xs font-bold text-teal-600 bg-teal-50 py-2 rounded-xl border border-teal-100">
                                    <span>✓ Selected File Bound</span>
                                </div>
                                <input type="file" name="surat_izin" id="izin-input" class="hidden" accept=".pdf,image/*">
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="bg-white p-8 sm:p-10 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-50/40">
                <h3 class="text-lg font-black text-gray-900 mb-2 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-sm font-bold">4</span>
                    Property Showcase Gallery
                </h3>
                <p class="text-xs text-gray-400 mb-6 pl-10">Upload multiple photos to show off the dimensions, condition, and visual orientation of the booth space.</p>

                <div class="bg-gray-50 p-6 rounded-3xl border-2 border-dashed border-gray-200 text-center hover:border-orange-500 transition">
                    <label class="cursor-pointer block">
                        <div class="w-12 h-12 bg-white text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-sm group-hover:scale-110 transition">
                            📸
                        </div>
                        <span class="text-xs font-bold text-gray-900 block">Choose Multiple Images</span>
                        <span class="text-[10px] text-gray-400 block mt-1">Hold CTRL/CMD to highlight up to 4 images at once</span>
                        <input type="file" name="photos[]" id="gallery-input" multiple accept="image/*" class="hidden" required>
                    </label>
                </div>

                <div id="gallery-preview-grid" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6"></div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" id="submit-button" class="bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-black text-base py-4 px-12 rounded-2xl shadow-xl shadow-orange-500/25 transition-all outline-none">
                    Submit Space Listing
                </button>
            </div>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            function bindDocInput(inputId, placeholderId, containerId) {
                const input = document.getElementById(inputId);
                const placeholder = document.getElementById(placeholderId);
                const container = document.getElementById(containerId);

                if(!input || !placeholder || !container) return;

                input.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        placeholder.classList.add('hidden');
                        container.classList.remove('hidden');
                        container.innerHTML = `<span>✓ Uploaded: <span class="underline">${this.files[0].name}</span></span>`;
                    } else {
                        placeholder.classList.remove('hidden');
                        container.classList.add('hidden');
                    }
                });
            }

            bindDocInput('tanah-input', 'tanah-placeholder', 'tanah-preview-container');
            bindDocInput('izin-input', 'izin-placeholder', 'izin-preview-container');

          
            const galleryInput = document.getElementById('gallery-input');
            const galleryGrid = document.getElementById('gallery-preview-grid');

            if (galleryInput && galleryGrid) {
                galleryInput.addEventListener('change', function() {
                    galleryGrid.innerHTML = ''; 
                    
                    const files = Array.from(this.files).slice(0, 4);
                    
                    files.forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const isPrimaryBadge = index === 0 ? 
                                '<span class="absolute top-2 left-2 bg-orange-500 text-white text-[9px] font-extrabold px-2 py-0.5 rounded shadow">COVER</span>' : '';
                            
                            galleryGrid.innerHTML += `
                                <div class="relative bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm animate-fade-in group">
                                    ${isPrimaryBadge}
                                    <img src="${e.target.result}" class="w-full h-24 object-cover" alt="Staged Preview">
                                    <div class="p-2 bg-gray-50 border-t border-gray-100">
                                        <input type="text" name="photo_descriptions[]" placeholder="Caption caption..." 
                                               class="w-full bg-white border border-gray-200 rounded-lg px-2 py-1 text-[10px] outline-none focus:border-orange-500 font-medium text-gray-800">
                                    </div>
                                </div>
                            `;
                        };
                        reader.readAsDataURL(file);
                    });
                });
            }

            const form = document.getElementById('space-form');
            const submitBtn = document.getElementById('submit-button');

            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    submitBtn.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Buffering Assets...
                        </span>
                    `;
                });
            }
        });
    </script>
</x-user-layout>