<x-user-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Incoming Reservation Requests</h1>
            <p class="text-gray-500 font-medium mt-2">Manage applications and ongoing rentals for your properties.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-teal-50 text-teal-700 p-4 rounded-xl border border-teal-100 font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($requests->count() > 0)
            <div class="space-y-4">
                @foreach($requests as $request)
                    <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row gap-6 items-stretch">
                        
                        <div class="w-full md:w-56 lg:w-64 aspect-video md:aspect-auto flex-shrink-0 rounded-2xl overflow-hidden bg-gray-100 relative">
                            <img src="{{ $request->space->cover_photo_url }}" alt="{{ $request->space->name }}" class="absolute inset-0 w-full h-full object-cover">
                        </div>

                        <div class="flex-1 min-w-0 py-2">
                            <div class="flex flex-wrap justify-between items-start gap-4 mb-2">
                                <div class="min-w-0">
                                    <h3 class="text-xl font-black text-gray-900 truncate">{{ $request->space->name }}</h3>
                                    <p class="text-sm font-bold text-gray-500 flex items-center gap-2 mt-1 truncate">
                                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-black uppercase">
                                            Applicant: {{ $request->renter->name }}
                                        </span>
                                    </p>
                                </div>

                                <div class="flex-shrink-0">
                                    @php
                                        $latestMsg = $request->messages->sortByDesc('created_at')->first();
                                        $isMyTurn = $request->status_id == \App\Models\Status::RNT_REQ_PENDING && $latestMsg && $latestMsg->sender_id !== Auth::id();
                                        $waitingForRenter = $request->status_id == \App\Models\Status::RNT_REQ_PENDING && $latestMsg && $latestMsg->sender_id === Auth::id();
                                    @endphp

                                    @if($isMyTurn)
                                        <span class="bg-orange-50 text-orange-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider">Action Required</span>
                                    @elseif($waitingForRenter)
                                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider">Waiting for Renter</span>
                                    @elseif($request->status_id == \App\Models\Status::RNT_REQ_ACCEPTED)
                                        <span class="bg-teal-50 text-teal-600 px-3 py-1.5 rounded-lg text-xs font-black border border-teal-100 tracking-wide uppercase">Accepted</span>
                                    @elseif($request->status_id == \App\Models\Status::RNT_REQ_REJECTED)
                                        <span class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-xs font-black border border-red-100 tracking-wide uppercase">Rejected</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg text-xs font-black border border-gray-200 tracking-wide uppercase">{{ $request->status->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6 py-4 border-t border-gray-50">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total Revenue</p>
                                    <p class="text-sm font-black text-orange-500">Rp {{ number_format($request->total_price, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Start Date</p>
                                    <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">End Date</p>
                                    <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</p>
                                </div>
                                @if($request->visit_date)
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Proposed Visit</p>
                                        <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($request->visit_date)->format('M d, Y') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="w-full md:w-40 flex-shrink-0 flex md:flex-col justify-center gap-3 border-t md:border-t-0 md:border-l border-gray-50 pt-4 md:pt-0 md:pl-6">
                            <a href="{{ route('owner.reservations.show', $request->id) }}" class="w-full text-center bg-gray-900 hover:bg-black text-white px-5 py-3 rounded-xl font-bold text-sm transition shadow-sm">
                                Review Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $requests->links() }}
            </div>
        @else
            <div class="bg-white border border-gray-100 rounded-[2rem] p-12 text-center shadow-sm flex flex-col items-center">
                <span class="text-6xl mb-4">📥</span>
                <h3 class="text-xl font-black text-gray-900 mb-2">No incoming requests yet</h3>
                <p class="text-gray-500 font-medium mb-6">When someone applies to rent one of your spaces, it will appear here.</p>
                <a href="{{ route('owner.spaces.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold transition active:scale-95">
                    Manage My Spaces
                </a>
            </div>
        @endif
    </div>
</x-user-layout>