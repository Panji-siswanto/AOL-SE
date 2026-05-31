<x-admin-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="{ viewingDoc: null }">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">User Verifications</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Manage and audit identity verification requests for platform access.
                </p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
            
            <div class="bg-gray-100 p-1.5 rounded-2xl inline-flex flex-wrap shrink-0">
                <a href="{{ route('admin.user-verifications.index') }}"
                   class="px-5 py-2.5 text-xs font-extrabold rounded-xl transition-all {{ request()->routeIs('admin.user-verifications.index') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                    ⏳ Pending Queue
                </a>
                <a href="{{ route('admin.user-verifications.history') }}"
                   class="px-5 py-2.5 text-xs font-extrabold rounded-xl transition-all {{ request()->routeIs('admin.user-verifications.history') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                    📁 Processed History
                </a>
            </div>

          <form method="GET" action="{{ route('admin.user-verifications.history') }}" class="flex flex-wrap w-full lg:w-auto items-center gap-2">
                
                <div class="relative w-full sm:w-auto lg:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." 
                           class="w-full bg-white border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 rounded-xl pl-4 pr-10 py-2.5 text-xs font-medium text-gray-900 outline-none transition">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-teal-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
                
                <select name="status" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-teal-200 focus:border-teal-500 outline-none cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="usr_verified" {{ request('status') === 'usr_verified' ? 'selected' : '' }}>Approved</option>
                    <option value="usr_rejected" {{ request('status') === 'usr_rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="sort_date" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-teal-200 focus:border-teal-500 outline-none cursor-pointer">
                    <option value="newest" {{ request('sort_date', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort_date') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                </select>

                @if(request('search') || request('status') || request('sort_date') === 'oldest')
                    <a href="{{ route('admin.user-verifications.history') }}" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-bold transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                            <th class="py-4 px-6">User Information</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6">Admin Notes / Feedback</th> <th class="py-4 px-6">Processed By</th>
                            <th class="py-4 px-6">Assets</th>
                            <th class="py-4 px-6 text-right">Processed At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        @forelse($historicalLogs as $log)
                            @php
                                $ktpDoc = $log->documents->firstWhere('documentType.code', 'ktp');
                                $selfieDoc = $log->documents->firstWhere('documentType.code', 'selfie_ktp');
                                $isApproved = $log->status_id == \App\Models\Status::USR_VERIFIED;
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
                                    @if($isApproved)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-black bg-teal-50 text-teal-700 border border-teal-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-black bg-red-50 text-red-700 border border-red-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                                        </span>
                                    @endif
                                </td>

                                <td class="py-5 px-6">
                                    @if($log->note)
                                        <p class="text-xs text-gray-600 font-medium max-w-xs leading-relaxed line-clamp-3" title="{{ $log->note }}">
                                            {{ $log->note }}
                                        </p>
                                    @else
                                        <span class="text-gray-300 text-xs italic">- No notes provided -</span>
                                    @endif
                                </td>

                                <td class="py-5 px-6">
                                    <div class="font-bold text-gray-800 text-xs">{{ $log->admin->name ?? 'System' }}</div>
                                </td>

                                <td class="py-5 px-6">
                                    <div class="flex gap-2">
                                        @if($ktpDoc && $ktpDoc->file_path)
                                            <button @click="viewingDoc = '{{ asset('storage/' . $ktpDoc->file_path) }}'" class="text-xs font-bold text-gray-500 hover:text-orange-500 bg-gray-100 hover:bg-orange-50 px-2.5 py-1.5 rounded-lg transition border border-transparent hover:border-orange-100" title="View KTP">
                                                KTP
                                            </button>
                                        @endif
                                        @if($selfieDoc && $selfieDoc->file_path)
                                            <button @click="viewingDoc = '{{ asset('storage/' . $selfieDoc->file_path) }}'" class="text-xs font-bold text-gray-500 hover:text-orange-500 bg-gray-100 hover:bg-orange-50 px-2.5 py-1.5 rounded-lg transition border border-transparent hover:border-orange-100" title="View Selfie">
                                                Selfie
                                            </button>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-5 px-6 text-right text-xs text-gray-500 font-medium">
                                    {{ $log->updated_at->format('M d, Y') }}
                                    <span class="block text-[10px] text-gray-400 mt-0.5">
                                        {{ $log->updated_at->format('H:i') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="text-4xl mb-3">📭</div>
                                    <div class="font-extrabold text-gray-900 text-base">No Matching Records</div>
                                    <p class="text-xs text-gray-400 mt-1">We couldn't find any historical verifications matching your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($historicalLogs->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $historicalLogs->links() }}
                </div>
            @endif
        </div>

        <div x-show="viewingDoc" 
             x-transition.opacity
             class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" 
             @click.self="viewingDoc = null" 
             @keydown.escape.window="viewingDoc = null"
             style="display: none;">
            <div class="relative max-w-3xl max-h-[90vh] bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col">
                <button @click="viewingDoc = null" class="absolute top-4 right-4 bg-gray-900/80 hover:bg-gray-900 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm backdrop-blur transition z-10">✕</button>
                <div class="p-2 overflow-auto flex-grow flex items-center justify-center bg-gray-900">
                    <img :src="viewingDoc" class="max-w-full max-h-[80vh] object-contain rounded-2xl" alt="Secure Document Preview">
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>