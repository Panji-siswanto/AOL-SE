<x-admin-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="{ rejectingId: null, inspectingListing: null }">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Space Listings Moderation</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Review incoming space registry applications, verify commercial viability, and confirm owner credentials before publishing to the marketplace.
                </p>
            </div>
            <div class="bg-teal-50 border border-teal-100 px-4 py-2 rounded-xl text-xs font-extrabold text-teal-700 self-stretch sm:self-auto text-center">
                Pending Listings: {{ $requests->count() }}
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-teal-50 border border-teal-100 rounded-2xl text-teal-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-800 text-sm font-bold flex items-center gap-3 shadow-sm">
                <span>❌</span> {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                            <th class="py-4 px-6">Space Details</th>
                            <th class="py-4 px-6">Location Mapping</th>
                            <th class="py-4 px-6">Owner Identity Status</th>
                            <th class="py-4 px-6">Proposed Price</th>
                            <th class="py-4 px-6 text-right">Moderation Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        @forelse($requests as $space)
                            <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                
                                <td class="py-5 px-6">
                                    <div class="font-extrabold text-gray-900">{{ $space->name }}</div>
                                    <div class="text-xs text-gray-400 font-medium mt-0.5 line-clamp-1 max-w-xs">{{ $space->description }}</div>
                                    <div class="inline-block mt-2 text-[10px] bg-orange-50 text-orange-700 font-bold px-2 py-0.5 rounded border border-orange-100">
                                        Size: {{ $space->size }}
                                    </div>
                                </td>

                                <td class="py-5 px-6">
                                    <div class="font-bold text-gray-800 text-xs">{{ $space->location->city ?? 'City Not Provided' }}</div>
                                    <div class="text-[11px] text-gray-400 mt-0.5">{{ $space->location->province ?? '-' }}</div>
                                    <button @click="inspectingListing = {{ $space->toJson() }}" 
                                            class="mt-1 text-[11px] font-bold text-teal-600 hover:text-teal-700 block underline">
                                        View Full Address Map
                                    </button>
                                </td>

                                <td class="py-5 px-6">
                                    <div class="font-bold text-xs text-gray-900">{{ $space->owner->name ?? 'Unknown Owner' }}</div>
                                    @if($space->owner && $space->owner->ver_status == \App\Models\Status::USR_VERIFIED)
                                        <span class="inline-flex items-center gap-1 mt-1 text-[10px] bg-teal-50 text-teal-700 font-extrabold px-2 py-0.5 rounded-full">
                                            <span>✓</span> ID Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 mt-1 text-[10px] bg-red-50 text-red-600 font-extrabold px-2 py-0.5 rounded-full">
                                            <span>⚠️</span> Unverified Owner
                                        </span>
                                    @endif
                                </td>

                                <td class="py-5 px-6 font-black text-gray-900">
                                    Rp {{ number_format($space->price, 0, ',', '.') }}
                                    <span class="block text-[10px] text-gray-400 font-normal">per month</span>
                                </td>

                                <td class="py-5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        
                                        <button @click="rejectingId = (rejectingId === {{ $space->id }} ? null : {{ $space->id }})"
                                                class="px-3 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl font-bold text-xs transition">
                                            Reject
                                        </button>

                                        @if($space->owner && $space->owner->ver_status == \App\Models\Status::USR_VERIFIED)
                                            <form action="{{ route('admin.listing-requests.approve', $space->id) }}" method="POST" 
                                                  onsubmit="return confirm('Approve this listing? This will automatically promote the user to an Owner role and publish the space directly to marketplace catalogs.');">
                                                @csrf
                                                <input type="hidden" name="note" value="Listing reviewed and vetted by secure admin authorization protocols.">
                                                <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-black text-xs shadow-sm transition active:scale-95">
                                                    Approve
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('admin.user-verifications.index') }}" 
                                               class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl font-black text-xs shadow-sm transition inline-flex items-center gap-1.5"
                                               title="Review this owner's pending KTP documents">
                                                <span>⚠️</span> Verify Owner First
                                            </a>
                                        @endif

                                    </div>
                                </td>
                            </tr>

                            <tr x-show="rejectingId === {{ $space->id }}" x-transition class="bg-red-50/50 border-y border-red-100">
                                <td colspan="5" class="py-4 px-6">
                                    <form action="{{ route('admin.listing-requests.reject', $space->id) }}" method="POST" class="flex flex-col sm:flex-row gap-3 items-end sm:items-center justify-end">
                                        @csrf
                                        <div class="w-full sm:w-auto flex-grow max-w-xl">
                                            <label class="block text-[10px] font-black uppercase text-red-800 mb-1">Moderation Log / Rejection Note (Required):</label>
                                            <input type="text" name="note" required placeholder="e.g., Incomplete documentation / Spot resides inside illegal municipal sidewalk spaces." 
                                                   class="w-full bg-white border border-red-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-red-400 outline-none">
                                        </div>
                                        <div class="flex gap-2 shrink-0">
                                            <button type="button" @click="rejectingId = null" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-xs font-bold">
                                                Cancel
                                            </button>
                                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-xl shadow-sm transition">
                                                Reject Application
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="text-4xl mb-3">📋</div>
                                    <div class="font-extrabold text-gray-900 text-base">Listing Review Queue is Empty</div>
                                    <p class="text-xs text-gray-400 mt-1">There are no new commercial space applications awaiting publishing review at this time.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="inspectingListing" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" 
             @click.self="inspectingListing = null" 
             @keydown.escape.window="inspectingListing = null"
             x-cloak>
            <div class="relative w-full max-w-2xl bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                
                <div class="p-6 bg-gray-900 text-white flex justify-between items-center">
                    <div>
                        <span class="text-[10px] font-extrabold bg-teal-500 text-gray-900 px-2 py-0.5 rounded uppercase">Full Spec Profile</span>
                        <h3 class="text-xl font-black mt-1" x-text="inspectingListing?.name"></h3>
                    </div>
                    <button @click="inspectingListing = null" class="text-gray-400 hover:text-white font-bold text-lg p-1">✕</button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4 flex-grow text-sm">
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Description Overview</span>
                        <p class="text-gray-800 leading-relaxed text-xs bg-gray-50 p-3 rounded-xl border border-gray-100" x-text="inspectingListing?.description"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-50">
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Exact Dimensions</span>
                            <span class="font-extrabold text-gray-900 text-base" x-text="inspectingListing?.size"></span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Proposed Rental Value</span>
                            <span class="font-extrabold text-teal-600 text-base">Rp <span x-text="new Intl.NumberFormat('id-ID').format(inspectingListing?.price || 0)"></span> / mo</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-50">
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Location Coordinates & Mapping</span>
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs space-y-2">
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-500">Address Line:</span>
                                <span class="font-medium text-gray-900 text-right" x-text="inspectingListing?.location?.address || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-500">City / Municipality:</span>
                                <span class="font-medium text-gray-900" x-text="inspectingListing?.location?.city || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-500">Province:</span>
                                <span class="font-medium text-gray-900" x-text="inspectingListing?.location?.province || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200/60 font-mono text-[10px] text-gray-400">
                                <span>Lat: <span x-text="inspectingListing?.location?.latitude || '0.000000'"></span></span>
                                <span>Lng: <span x-text="inspectingListing?.location?.longitude || '0.000000'"></span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-100 text-center">
                    <button @click="inspectingListing = null" class="w-full py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-bold text-xs transition">
                        Close Inspector Window
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>