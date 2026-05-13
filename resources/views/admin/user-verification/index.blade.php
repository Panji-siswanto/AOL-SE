<x-admin-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="{ rejectingId: null, viewingDoc: null }">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">User Verifications Review</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Validate the authenticity of identity cards (KTP) and selfie submissions before authorizing listing and space rental capabilities.
                </p>
            </div>
            <div class="bg-orange-50 border border-orange-100 px-4 py-2 rounded-xl text-xs font-extrabold text-orange-600 self-stretch sm:self-auto text-center">
                Pending Queue: {{ $pendingLogs->count() }}
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-teal-50 border border-teal-100 rounded-2xl text-teal-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>❌</span> Processing Failed: {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                            <th class="py-4 px-6">User Information</th>
                            <th class="py-4 px-6">Identity Document (KTP)</th>
                            <th class="py-4 px-6">Selfie Verification</th>
                            <th class="py-4 px-6">Submitted At</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        @forelse($pendingLogs as $log)
                            @php
                                // Extract child documents dynamically from the pre-loaded HasMany relationships
                                $ktpDoc = $log->documents->firstWhere('documentType.code', 'ktp');
                                $selfieDoc = $log->documents->firstWhere('documentType.code', 'selfie_ktp');
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                
                                <td class="py-5 px-6">
                                    <div class="font-extrabold text-gray-900">{{ $log->user->name ?? 'Deleted User' }}</div>
                                    <div class="text-xs text-gray-400 font-medium mt-0.5">{{ $log->user->email ?? '-' }}</div>
                                    <div class="inline-block mt-2 text-[10px] bg-gray-100 text-gray-600 font-bold px-2 py-0.5 rounded">
                                        Account ID: {{ $log->user_id }}
                                    </div>
                                </td>

                                <td class="py-5 px-6">
                                    @if($ktpDoc && $ktpDoc->file_path)
                                        <button @click="viewingDoc = '{{ asset('storage/' . $ktpDoc->file_path) }}'" 
                                                class="flex items-center gap-2 text-xs font-bold text-teal-600 hover:text-teal-700 bg-teal-50/50 hover:bg-teal-50 px-3 py-2 rounded-xl transition border border-teal-100/50"
                                                title="{{ $ktpDoc->desc ?? 'View Identity Card' }}">
                                            <span>📄</span> View KTP
                                        </button>
                                    @else
                                        <span class="text-xs text-red-400 italic font-medium">Missing asset</span>
                                    @endif
                                </td>

                                <td class="py-5 px-6">
                                    @if($selfieDoc && $selfieDoc->file_path)
                                        <button @click="viewingDoc = '{{ asset('storage/' . $selfieDoc->file_path) }}'" 
                                                class="flex items-center gap-2 text-xs font-bold text-teal-600 hover:text-teal-700 bg-teal-50/50 hover:bg-teal-50 px-3 py-2 rounded-xl transition border border-teal-100/50"
                                                title="{{ $selfieDoc->desc ?? 'View Selfie Verification' }}">
                                            <span>📸</span> View Selfie
                                        </button>
                                    @else
                                        <span class="text-xs text-red-400 italic font-medium">Missing asset</span>
                                    @endif
                                </td>

                                <td class="py-5 px-6 text-xs text-gray-500 font-medium">
                                    {{ $log->created_at->format('M d, Y - H:i') }}
                                    <span class="block text-[10px] text-gray-400 mt-0.5">
                                        {{ $log->created_at->diffForHumans() }}
                                    </span>
                                </td>

                                <td class="py-5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        
                                        <button @click="rejectingId = (rejectingId === {{ $log->id }} ? null : {{ $log->id }})"
                                                class="px-3 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl font-bold text-xs transition">
                                            Reject
                                        </button>

                                        <form action="{{ route('admin.user-verifications.approve', $log->id) }}" method="POST" onsubmit="return confirm('Approve identity verification for this user? Assets will automatically be promoted to normalized production tables.');">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-black text-xs shadow-sm transition active:scale-95">
                                                Approve
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                            <tr x-show="rejectingId === {{ $log->id }}" x-transition class="bg-red-50/50 border-y border-red-100">
                                <td colspan="5" class="py-4 px-6">
                                    <form action="{{ route('admin.user-verifications.reject', $log->id) }}" method="POST" class="flex flex-col sm:flex-row gap-3 items-end sm:items-center justify-end">
                                        @csrf
                                        <div class="w-full sm:w-auto flex-grow max-w-xl">
                                            <label class="block text-[10px] font-black uppercase text-red-800 mb-1">Reason for Rejection (Required):</label>
                                            <input type="text" name="reason" required placeholder="e.g., Image is too blurry / Identity numbers are illegible." 
                                                   class="w-full bg-white border border-red-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-red-400 outline-none">
                                        </div>
                                        <div class="flex gap-2 shrink-0">
                                            <button type="button" @click="rejectingId = null" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-xs font-bold">
                                                Cancel
                                            </button>
                                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-xl shadow-sm transition">
                                                Submit Rejection
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="text-4xl mb-3">🏖️</div>
                                    <div class="font-extrabold text-gray-900 text-base">Verification Queue is Empty</div>
                                    <p class="text-xs text-gray-400 mt-1">There are no pending identity documents awaiting review at this time.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="viewingDoc" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" 
             @click.self="viewingDoc = null" 
             @keydown.escape.window="viewingDoc = null"
             x-cloak>
            <div class="relative max-w-3xl max-h-[90vh] bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col">
                <button @click="viewingDoc = null" class="absolute top-4 right-4 bg-gray-900/80 hover:bg-gray-900 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm backdrop-blur transition z-10">
                    ✕
                </button>
                <div class="p-2 overflow-auto flex-grow flex items-center justify-center bg-gray-900">
                    <img :src="viewingDoc" class="max-w-full max-h-[80vh] object-contain rounded-2xl" alt="Secure Document Preview">
                </div>
                <div class="p-4 bg-white border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-500 font-medium">Click outside the image or press <kbd class="px-1.5 py-0.5 bg-gray-100 border rounded font-mono font-bold text-gray-700">ESC</kbd> to close.</p>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>