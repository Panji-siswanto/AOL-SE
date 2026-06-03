<x-user-layout>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Identity Verification</h1>
            <p class="text-sm text-gray-500 mt-2">
                Submit your official identity card (KTP) and a verification selfie to unlock secure space listing and rental capabilities.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 bg-teal-50 border border-teal-100 rounded-2xl text-teal-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>❌</span> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>❌</span> {{ $errors->first() }}
            </div>
        @endif

        @if($user->ver_status == \App\Models\Status::USR_REJECTED && $latestLog && $latestLog->note)
            <div class="mb-8 p-6 bg-red-50 border border-red-200 rounded-[2rem] flex flex-col sm:flex-row items-start sm:items-center gap-4 shadow-sm">
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center font-bold text-xl shrink-0">
                    ⚠️
                </div>
                <div>
                    <h4 class="font-extrabold text-red-900 text-base">Previous Verification Rejected</h4>
                    <p class="text-xs text-red-700 mt-1 font-medium">
                        Administrator Feedback: <span class="font-bold">{{ $latestLog->note }}</span>
                    </p>
                    <p class="text-xs text-red-500 mt-1">Please review the required adjustments above and upload clearer documentation below.</p>
                </div>
            </div>
        @endif

        @if($user->ver_status == \App\Models\Status::USR_VERIFY_PENDING)
            <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm text-center max-w-xl mx-auto">
                <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse text-3xl">
                    ⏳
                </div>
                <h3 class="text-xl font-extrabold text-gray-900">Verification Under Review</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                    Your identity documents have been submitted successfully and are queued for secure administrative review. Your account roles and permissions will automatically update upon approval.
                </p>
                <div class="mt-8 pt-6 border-t border-gray-50 flex justify-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold bg-gray-900 text-white px-5 py-2.5 rounded-xl hover:bg-gray-800 transition">
                        &larr; Return to Dashboard
                    </a>
                </div>
            </div>
        @else
            <form action="{{ route('verification.store') }}" method="POST" enctype="multipart/form-data" id="verification-form" class="space-y-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="relative bg-white p-4 rounded-[2rem] border-2 border-dashed border-gray-200 hover:border-orange-500 transition-all group overflow-hidden shadow-sm">
                        <label class="cursor-pointer block">
                            <div id="ktp-placeholder" class="text-center py-10">
                                <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <span class="text-lg font-bold text-gray-900 block">Identity Card (KTP) Photo</span>
                                <p class="text-xs text-gray-400 mt-1">Format: JPG/PNG, Max: 2MB</p>
                            </div>
                            
                            <img id="ktp-preview" class="hidden w-full h-48 object-cover rounded-2xl mb-2" alt="KTP Validation Preview">
                            
                            <input type="file" name="ktp" id="ktp-input" class="hidden" accept="image/jpeg,image/png,image/jpg" required>
                        </label>
                    </div>

                    <div class="relative bg-white p-4 rounded-[2rem] border-2 border-dashed border-gray-200 hover:border-orange-500 transition-all group overflow-hidden shadow-sm">
                        <label class="cursor-pointer block">
                            <div id="selfie-placeholder" class="text-center py-10">
                                <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <span class="text-lg font-bold text-gray-900 block">Selfie Holding KTP</span>
                                <p class="text-xs text-gray-400 mt-1">Ensure your face and identity text are completely visible</p>
                            </div>

                            <img id="selfie-preview" class="hidden w-full h-48 object-cover rounded-2xl mb-2" alt="Selfie Validation Preview">

                            <input type="file" name="selfie_ktp" id="selfie-input" class="hidden" accept="image/jpeg,image/png,image/jpg" required>
                        </label>
                    </div>

                </div>

                <div class="flex justify-center pt-6">
                    <button type="submit" id="submit-btn" class="bg-orange-500 text-white px-12 py-4 rounded-2xl font-black text-lg shadow-xl shadow-orange-500/40 hover:bg-orange-600 active:scale-95 transition-all">
                        Submit Verification
                    </button>
                </div>
            </form>
        @endif
    </div>

    <script>
        function setupPreview(inputId, previewId, placeholderId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById(placeholderId);

            if (!input || !preview || !placeholder) return;

            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Enforce immediate feedback if file breaks standard payload restrictions
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Selected file exceeds the maximum 2MB size limit. Please upload a compressed version.');
                        this.value = ''; // Reset file input buffer
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

            // Form Submit Listener to completely block double-click/spam issues
            const form = document.getElementById('verification-form');
            const submitBtn = document.getElementById('submit-btn');

            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    // Immediately lock the UI component to prevent parallel requests during image asset buffer upload
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    submitBtn.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading Assets...
                        </span>
                    `;
                });
            }
        });
    </script>
</x-user-layout>