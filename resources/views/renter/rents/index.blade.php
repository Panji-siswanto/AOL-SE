<x-user-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data>
        <style> [x-cloak] { display: none !important; } </style>
        
        <div class="mb-8">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">My Rent Requests</h1>
        </div>

        @if(session('success')) <div class="mb-6 bg-teal-50 text-teal-700 p-4 rounded-xl border border-teal-100 font-bold text-sm shadow-sm">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 font-bold text-sm shadow-sm">{{ session('error') }}</div> @endif

        @if($requests->count() > 0)
            <div class="space-y-4">
                @foreach($requests as $request)
                    @php
                        $pendingId = \App\Models\Status::where('code', 'rnt_req_pending')->value('id');
                        $acceptedId = \App\Models\Status::where('code', 'rnt_req_accepted')->value('id');
                        $rejectedId = \App\Models\Status::where('code', 'rnt_req_rejected')->value('id');
                        $cancelledId = \App\Models\Status::where('code', 'rnt_req_cancelled')->value('id');
                        
                        $latestMessage = $request->messages->sortByDesc('created_at')->first();
                        $isMyTurn = $request->status_id == $pendingId && $latestMessage && $latestMessage->sender_id !== Auth::id();
                        $waitingForOwner = $request->status_id == $pendingId && $latestMessage && $latestMessage->sender_id === Auth::id();
                    @endphp

                    <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row gap-6 items-stretch">
                        
                        <div class="w-full md:w-56 lg:w-64 aspect-video md:aspect-auto flex-shrink-0 rounded-2xl overflow-hidden bg-gray-100 relative">
                            <img src="{{ $request->space->cover_photo_url }}" class="absolute inset-0 w-full h-full object-cover">
                        </div>

                        <div class="flex-1 min-w-0 py-2">
                            <div class="flex flex-wrap justify-between items-start gap-4 mb-2">
                                <div class="min-w-0">
                                    <h3 class="text-xl font-black text-gray-900 truncate">{{ $request->space->name }}</h3>
                                    <p class="text-sm font-medium text-gray-500 flex items-center gap-1 mt-1 truncate">
                                        <span class="text-teal-600">📍</span> {{ $request->space->location->address }}, {{ $request->space->location->city }}
                                    </p>
                                </div>

                                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                    @if($isMyTurn) <span class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-lg text-xs font-black border border-orange-100 uppercase">Action Required</span>
                                    @elseif($waitingForOwner) <span class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-black border border-blue-100 uppercase">Waiting for Owner</span>
                                    @elseif($request->status_id == $acceptedId) <span class="bg-teal-50 text-teal-600 px-3 py-1.5 rounded-lg text-xs font-black border border-teal-100 uppercase">Accepted</span>
                                    @elseif($request->status_id == $rejectedId) <span class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-xs font-black border border-red-100 uppercase">Rejected</span>
                                    @elseif($request->status_id == $cancelledId) <span class="bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-xs font-black border border-gray-200 uppercase">Cancelled</span>
                                    @else <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg text-xs font-black border border-gray-200 uppercase">{{ $request->status->name }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($latestMessage && $latestMessage->message)
                                <div class="mt-5 bg-gray-50 border border-gray-200 rounded-3xl p-4 text-sm text-gray-700">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <span class="font-black uppercase tracking-wide text-gray-500 text-[10px]">Latest Message From {{ $latestMessage->sender_id == Auth::id() ? 'You' : 'Owner' }}</span>
                                        <span class="text-[10px] opacity-75">{{ \Carbon\Carbon::parse($latestMessage->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <p class="whitespace-pre-line font-medium">{{ $latestMessage->message }}</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6 py-4 border-t border-gray-50">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total Price</p>
                                    <p class="text-sm font-bold text-teal-600">Rp {{ number_format($request->total_price, 0, ',', '.') }}</p>
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
                                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Visit Date</p>
                                        <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($request->visit_date)->format('M d, Y') }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- The 3 Core Actions --}}
                            @if($isMyTurn)
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-4">
                                    <form method="POST" action="{{ route('rents.approve', $request->id) }}">
                                        @csrf <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2.5 rounded-xl font-black text-sm transition shadow-sm">Accept Dates</button>
                                    </form>
                                    <button @click.prevent="$dispatch('open-counter-modal-{{ $request->id }}')" class="w-full bg-white text-blue-700 border border-blue-200 hover:bg-blue-50 px-3 py-2.5 rounded-xl font-black text-sm transition">Propose New</button>
                                    <button @click.prevent="$dispatch('open-cancel-modal-{{ $request->id }}')" class="w-full bg-white text-red-600 border border-red-200 hover:bg-red-50 px-3 py-2.5 rounded-xl font-black text-sm transition">Decline & Cancel</button>
                                </div>
                            @elseif($waitingForOwner)
                                <div class="flex justify-end mt-4">
                                    <button @click.prevent="$dispatch('open-cancel-modal-{{ $request->id }}')" class="text-[10px] font-black uppercase tracking-wider text-red-500 hover:text-red-700 underline transition">Withdraw Application</button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 1. Cancel Confirmation Modal --}}
                    @if($request->status_id == $pendingId)
                    <div x-data="{ showCancel: false }" @open-cancel-modal-{{ $request->id }}.window="showCancel = true" @keydown.escape.window="showCancel = false" class="relative z-50" x-cloak>
                        <div x-show="showCancel" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                        <div x-show="showCancel" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div @click.away="showCancel = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-8">
                                    <h3 class="text-2xl font-black text-gray-900 mb-2">Cancel Application</h3>
                                    <p class="text-sm text-gray-500 font-medium mb-6">Are you sure you want to completely withdraw your application? This action cannot be undone.</p>
                                    <div class="flex gap-3">
                                        <button @click="showCancel = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3 font-bold transition">No, Keep It</button>
                                        <form action="{{ route('rents.reject', $request->id) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white rounded-xl py-3 font-black transition active:scale-95">Yes, Cancel</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 2. Dynamic Counter-Propose Modal --}}
                    @if($isMyTurn)
                    <div x-data="{ 
                            showCounter: false,
                            startDate: '{{ $request->start_date }}', endDate: '{{ $request->end_date }}', visitDate: '{{ $request->visit_date }}',
                            pricingCode: '{{ strtolower($request->pricing->pricingType->code) }}', pricePerUnit: {{ $request->pricing->price }},
                            get durationText() {
                                if(!this.startDate || !this.endDate) return '0 Days';
                                let s = new Date(this.startDate); let e = new Date(this.endDate);
                                let diffTime = e - s; if (diffTime <= 0) return '0 Days';
                                let totalDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                                if(this.pricingCode === 'daily') return totalDays + ' Day(s)';
                                if(this.pricingCode === 'weekly') { let w = Math.floor(totalDays / 7); let d = totalDays % 7; let text = []; if (w > 0) text.push(w + ' Week(s)'); if (d > 0) text.push(d + ' Day(s)'); return text.join(', '); }
                                if(this.pricingCode === 'monthly') { let m = Math.floor(totalDays / 30); let remDays = totalDays % 30; let w = Math.floor(remDays / 7); let d = remDays % 7; let text = []; if (m > 0) text.push(m + ' Month(s)'); if (w > 0) text.push(w + ' Week(s)'); if (d > 0) text.push(d + ' Day(s)'); return text.join(', '); }
                                return '0 Days';
                            },
                            get total() {
                                if(!this.startDate || !this.endDate) return 0;
                                let s = new Date(this.startDate); let e = new Date(this.endDate);
                                let diffTime = e - s; if (diffTime <= 0) return 0;
                                let totalDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                                if(this.pricingCode === 'weekly') return Math.round((this.pricePerUnit / 7) * totalDays);
                                if(this.pricingCode === 'monthly') return Math.round((this.pricePerUnit / 30) * totalDays);
                                return totalDays * this.pricePerUnit;
                            }
                        }" 
                        @open-counter-modal-{{ $request->id }}.window="showCounter = true" @keydown.escape.window="showCounter = false" class="relative z-50" x-cloak>
                        
                        <div x-show="showCounter" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                        <div x-show="showCounter" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div @click.away="showCounter = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-8">
                                    <h3 class="text-xl font-black text-gray-900 mb-2">Counter-Propose Dates</h3>
                                    
                                    <div class="bg-blue-50 p-4 rounded-xl mb-4 border border-blue-100 flex justify-between items-center mt-4">
                                        <div>
                                            <p class="text-[10px] uppercase font-black text-blue-400">Total Duration</p>
                                            <p class="text-sm font-black text-blue-900" x-text="durationText"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] uppercase font-black text-blue-400">Prorated Total Price</p>
                                            <p class="text-lg font-black text-teal-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total)"></p>
                                        </div>
                                    </div>

                                    <form action="{{ route('rents.reschedule', $request->id) }}" method="POST">
                                        @csrf
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Visit Date (Optional)</label>
                                                <input type="date" name="new_visit_date" x-model="visitDate" :max="startDate" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Start Date *</label>
                                                <input type="date" name="new_start_date" required x-model="startDate" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">End Date *</label>
                                                <input type="date" name="new_end_date" required x-model="endDate" :min="startDate" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Message</label>
                                            <textarea name="response_note" rows="3" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                        </div>
                                        <div class="mt-6 flex gap-3">
                                            <button type="submit" :disabled="total === 0" :class="total === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700 active:scale-95'" class="w-full bg-blue-600 text-white rounded-xl py-3 font-black transition">Send Proposal</button>
                                            <button type="button" @click="showCounter = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3 font-bold transition">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            <div class="mt-8">{{ $requests->links() }}</div>
        @else
            {{-- Empty HTML --}}
        @endif
    </div>
</x-user-layout>