<x-user-layout>
    {{-- Initialize Alpine with the active tab from the controller/URL --}}
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" x-data="{ activeTab: '{{ $activeTab }}' }">
        
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Property Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your active marketplace listings and track registration applications.</p>
            </div>
            
            <a href="{{ route('space-registrations.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-orange-500/30 transition-all active:scale-95 flex items-center gap-2">
                <span>+</span> List Another Space
            </a>
        </div>

        {{-- Tab Navigation --}}
        <div class="flex gap-4 mb-6 border-b border-gray-200">
            <button @click="activeTab = 'live'" 
                    :class="activeTab === 'live' ? 'border-[#009485] text-[#009485]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="pb-4 px-2 border-b-2 font-bold text-sm transition-all flex items-center gap-2">
                Live Spaces 
                <span class="bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-[10px]">{{ $spaces->count() }}</span>
            </button>
            <button @click="activeTab = 'applications'" 
                    :class="activeTab === 'applications' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="pb-4 px-2 border-b-2 font-bold text-sm transition-all flex items-center gap-2">
                Registration Applications
                <span class="bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-[10px]">{{ $registrations->count() }}</span>
            </button>
        </div>

        {{-- TAB 1: Live Spaces --}}
        <div x-show="activeTab === 'live'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            
            {{-- Search & Filter for LIVE SPACES --}}
            <form method="GET" action="{{ route('space-registrations.index') }}" class="flex flex-wrap w-full items-center gap-3 mb-6 bg-gray-50 p-3 rounded-2xl border border-gray-100">
                <input type="hidden" name="tab" value="live">
                
                <div class="relative flex-grow sm:max-w-xs">
                    <input type="text" name="search" value="{{ request('tab') === 'live' ? request('search') : '' }}" placeholder="Search live spaces..." 
                           class="w-full bg-white border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 rounded-xl pl-4 pr-10 py-2.5 text-sm font-medium text-gray-900 outline-none transition">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-teal-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
                
                <select name="status" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-teal-200 focus:border-teal-500 outline-none cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="spc_available" {{ request('tab') === 'live' && request('status') === 'spc_available' ? 'selected' : '' }}>Available</option>
                    <option value="spc_unavailable" {{ request('tab') === 'live' && request('status') === 'spc_unavailable' ? 'selected' : '' }}>Unavailable</option>
                </select>

                <select name="sort_date" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-teal-200 focus:border-teal-500 outline-none cursor-pointer">
                    <option value="newest" {{ request('tab') === 'live' && request('sort_date', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('tab') === 'live' && request('sort_date') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                </select>

                @if(request('tab') === 'live' && (request('search') || request('status') || request('sort_date') === 'oldest'))
                    <a href="{{ route('space-registrations.index', ['tab' => 'live']) }}" class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-sm font-bold transition">
                        Clear
                    </a>
                @endif
            </form>

            {{-- Live Spaces Grid --}}
            @if($spaces->isEmpty())
                <div class="bg-white rounded-[2rem] border border-gray-100 p-12 text-center shadow-sm">
                    <div class="w-20 h-20 bg-teal-50 text-teal-500 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-4">🏪</div>
                    <h3 class="text-lg font-black text-gray-900">No Live Spaces</h3>
                    <p class="text-gray-500 text-sm mt-2 max-w-md mx-auto">You don't have any spaces active on the public marketplace. Check your applications tab or submit a new listing.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($spaces as $space)
                        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-5 hover:shadow-xl transition-all">
                            <div class="w-full h-40 bg-gray-100 rounded-2xl mb-4 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1582037928769-181f2644ecb7?auto=format&fit=crop&q=80&w=600" class="w-full h-full object-cover opacity-80">
                                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $space->status->code === 'spc_available' ? 'text-teal-600' : 'text-red-500' }}">
                                    {{ $space->status->name }}
                                </div>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 truncate">{{ $space->name }}</h3>
                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                📍 {{ $space->location->city }}, {{ $space->location->province }}
                            </p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center">
                                <span class="text-sm font-black text-gray-900">Rp {{ number_format($space->price, 0, ',', '.') }}</span>
                                <button class="text-xs font-bold text-[#009485] bg-teal-50 px-4 py-2 rounded-xl hover:bg-teal-100 transition">Manage</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB 2: Applications --}}
        <div x-show="activeTab === 'applications'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            
            {{-- Search & Filter for APPLICATIONS --}}
            <form method="GET" action="{{ route('space-registrations.index') }}" class="flex flex-wrap w-full items-center gap-3 mb-6 bg-gray-50 p-3 rounded-2xl border border-gray-100">
                <input type="hidden" name="tab" value="applications">
                
                <div class="relative flex-grow sm:max-w-xs">
                    <input type="text" name="search" value="{{ request('tab') === 'applications' ? request('search') : '' }}" placeholder="Search applications..." 
                           class="w-full bg-white border border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 rounded-xl pl-4 pr-10 py-2.5 text-sm font-medium text-gray-900 outline-none transition">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-orange-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
                
                <select name="status" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="reg_pending" {{ request('tab') === 'applications' && request('status') === 'reg_pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="reg_approved" {{ request('tab') === 'applications' && request('status') === 'reg_approved' ? 'selected' : '' }}>Approved</option>
                    <option value="reg_rejected" {{ request('tab') === 'applications' && request('status') === 'reg_rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="sort_date" onchange="this.form.submit()" class="bg-white border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none cursor-pointer">
                    <option value="newest" {{ request('tab') === 'applications' && request('sort_date', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('tab') === 'applications' && request('sort_date') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                </select>

                @if(request('tab') === 'applications' && (request('search') || request('status') || request('sort_date') === 'oldest'))
                    <a href="{{ route('space-registrations.index', ['tab' => 'applications']) }}" class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-sm font-bold transition">
                        Clear
                    </a>
                @endif
            </form>

            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                @if($registrations->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-4">📝</div>
                        <h3 class="text-lg font-black text-gray-900">No Applications Found</h3>
                        <p class="text-gray-500 text-sm mt-2">You haven't submitted any space registrations matching those filters.</p>
                    </div>
                @else
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-bold rounded-tl-[2rem]">Property Name</th>
                                <th class="px-6 py-4 font-bold">Location</th>
                                <th class="px-6 py-4 font-bold">Submitted</th>
                                <th class="px-6 py-4 font-bold">Status</th>
                                <th class="px-6 py-4 font-bold rounded-tr-[2rem] text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($registrations as $reg)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $reg->name }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $reg->size }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $reg->location->city }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $reg->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeColor = match($reg->status->code) {
                                                'reg_pending' => 'bg-orange-50 text-orange-600',
                                                'reg_approved' => 'bg-emerald-50 text-emerald-600',
                                                'reg_rejected' => 'bg-red-50 text-red-600',
                                                default => 'bg-gray-100 text-gray-600'
                                            };
                                        @endphp
                                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider {{ $badgeColor }}">
                                            {{ $reg->status->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('space-registrations.show', $reg->id) }}" class="text-xs font-bold text-gray-400 hover:text-gray-900 transition">View Details &rarr;</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    </div>
</x-user-layout>