<x-admin-layout>
    <style> [x-cloak] { display: none !important; } </style>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="reviewManager()">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <a href="{{ route('admin.dashboard') ?? '#' }}" class="text-sm font-bold text-gray-400 hover:text-teal-600 transition mb-2 inline-block">&larr; Back to Dashboard</a>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Space Listing History</h1>
                <p class="text-gray-500 text-sm mt-1">Audit log of previously processed space registrations and commercial listings.</p>
            </div>
        </div>

        {{-- Top Navigation & Filters --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
            
            {{-- Tabs --}}
            <div class="bg-gray-100 p-1.5 rounded-2xl inline-flex flex-wrap shrink-0">
                <a href="{{ route('admin.listing-requests.index') }}"
                   class="px-5 py-2.5 text-xs font-extrabold rounded-xl transition-all {{ request()->routeIs('admin.listing-requests.index') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                    ⏳ Pending Queue
                </a>
                <a href="{{ route('admin.listing-requests.history') }}"
                   class="px-5 py-2.5 text-xs font-extrabold rounded-xl transition-all {{ request()->routeIs('admin.listing-requests.history') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                    📁 Processed History
                </a>
            </div>

            {{-- Search, Status & Sort Form --}}
            <form method="GET" action="{{ route('admin.listing-requests.history') }}" class="flex flex-wrap w-full lg:w-auto items-center gap-2">
                
                <div class="relative w-full sm:w-auto lg:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search property or host..." 
                           class="w-full bg-white border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 rounded-xl pl-4 pr-10 py-2.5 text-xs font-medium text-gray-900 outline-none transition">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-teal-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
                
                <select name="status" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-teal-200 focus:border-teal-500 outline-none cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="reg_approved" {{ request('status') === 'reg_approved' ? 'selected' : '' }}>Approved</option>
                    <option value="reg_rejected" {{ request('status') === 'reg_rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="sort_date" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-teal-200 focus:border-teal-500 outline-none cursor-pointer">
                    <option value="newest" {{ request('sort_date', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort_date') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                </select>

                @if(request('search') || request('status') || request('sort_date') === 'oldest')
                    <a href="{{ route('admin.listing-requests.history') }}" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-bold transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- History Table --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                            <th class="py-4 px-6">Property Details</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6">Admin Notes</th>
                            <th class="py-4 px-6">Processed By</th>
                            <th class="py-4 px-6 text-right">Processed At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        @forelse($historicalRequests as $req)
                            @php
                                $latestLog = $req->logs->last();
                                $isApproved = $req->status->code === 'reg_approved';
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-gray-900">{{ $req->name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $req->owner->name }}</div>
                                    
                                    {{-- Open read-only modal --}}
                                    <button @click="openReview({{ $req->toJson() }})" class="mt-2 text-[10px] bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold px-2 py-1 rounded transition">
                                        View Data Snapshot
                                    </button>
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
                                    @if($latestLog && $latestLog->note)
                                        <p class="text-xs text-gray-600 font-medium max-w-xs leading-relaxed line-clamp-3" title="{{ $latestLog->note }}">
                                            {{ $latestLog->note }}
                                        </p>
                                    @else
                                        <span class="text-gray-300 text-xs italic">- No notes provided -</span>
                                    @endif
                                </td>

                                <td class="py-5 px-6">
                                    <div class="font-bold text-gray-800 text-xs">{{ $latestLog->admin->name ?? 'System' }}</div>
                                </td>

                                <td class="py-5 px-6 text-right text-xs text-gray-500 font-medium">
                                    {{ $req->updated_at->format('M d, Y') }}
                                    <span class="block text-[10px] text-gray-400 mt-0.5">{{ $req->updated_at->format('H:i') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="text-4xl mb-3">📭</div>
                                    <div class="font-extrabold text-gray-900 text-base">No Historical Records</div>
                                    <p class="text-xs text-gray-400 mt-1">We couldn't find any processed listing requests matching your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historicalRequests->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $historicalRequests->links() }}
                </div>
            @endif
        </div>

        {{-- Alpine Centered Pop-Up Modal (Read-Only Mode) --}}
        <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" x-show="isOpen" x-transition.opacity @click="closeReview()"></div>

                <div class="relative bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-3xl w-full flex flex-col max-h-[90vh]"
                     x-show="isOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop>
                    
                    <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                        <div>
                            <h2 class="text-2xl font-black text-gray-900">Application Snapshot</h2>
                            <p class="text-sm text-gray-500 mt-1" x-text="requestData ? 'ID: REQ-' + requestData.id + ' (Read-Only)' : ''"></p>
                        </div>
                        <button @click="closeReview()" class="text-gray-400 hover:text-gray-900 bg-white p-2.5 rounded-full shadow-sm border border-gray-200 transition">✕</button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 space-y-8" x-if="requestData">
                        
                        {{-- Host Info --}}
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Host Information</h3>
                            <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <div class="w-14 h-14 bg-teal-100 text-teal-700 rounded-xl flex items-center justify-center font-bold text-xl shadow-inner">
                                    <span x-text="requestData?.owner.name.charAt(0)"></span>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg" x-text="requestData?.owner.name"></p>
                                    <p class="text-sm text-gray-500" x-text="requestData?.owner.email"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Space Details --}}
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Property Details</h3>
                            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                                <h4 class="font-black text-xl text-gray-900 mb-1" x-text="requestData?.name"></h4>
                                <p class="text-sm font-bold text-teal-600 mb-4" x-text="requestData?.size"></p>
                                <p class="text-sm text-gray-600 whitespace-pre-line leading-relaxed" x-text="requestData?.description"></p>
                                <div class="mt-5 pt-5 border-t border-gray-100 flex items-start gap-3">
                                    <span class="text-xl">📍</span>
                                    <p class="text-sm text-gray-600 font-medium leading-tight" x-text="requestData?.location.address + ', ' + requestData?.location.city + ', ' + requestData?.location.province"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Legal Documents --}}
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Legal Assets on File</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="doc in requestData?.documents" :key="doc.id">
                                    <a :href="'/storage/' + doc.file_path" target="_blank" class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 hover:border-blue-400 hover:bg-blue-50 hover:shadow-md transition group">
                                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition shadow-sm">📄</div>
                                        <div>
                                            <p class="font-bold text-sm text-gray-900" x-text="doc.document_type.name"></p>
                                            <p class="text-xs text-gray-500 mt-1 line-clamp-1" x-text="doc.description"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>

                    </div>

                    {{-- No action buttons, just a simple close button since it's read-only --}}
                    <div class="p-6 border-t border-gray-100 bg-gray-50 shrink-0 flex justify-end">
                        <button @click="closeReview()" class="px-8 py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl transition shadow-sm">
                            Close Snapshot
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reviewManager', () => ({
                isOpen: false,
                requestData: null,

                openReview(data) {
                    this.requestData = data;
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden'; 
                },

                closeReview() {
                    this.isOpen = false;
                    setTimeout(() => { this.requestData = null; }, 300);
                    document.body.style.overflow = 'auto';
                }
            }));
        });
    </script>
</x-admin-layout>