<x-user-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data>
        <style> [x-cloak] { display: none !important; } </style>
        
        <div class="mb-8">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">My Rent Requests</h1>
            <p class="text-gray-500 font-medium mt-2">Track the status of your applications and ongoing rents.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-teal-50 text-teal-700 p-4 rounded-xl border border-teal-100 font-bold text-sm shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 font-bold text-sm shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($requests->count() > 0)
            <div class="space-y-4">
                @foreach($requests as $request)
                    @php
                        // "Whose turn is it?" Logic
                        $latestMessage = $request->messages->sortByDesc('created_at')->first();
                        $isMyTurn = $request->status_id == \App\Models\Status::RNT_REQ_PENDING && $latestMessage && $latestMessage->sender_id !== Auth::id();
                        $waitingForOwner = $request->status_id == \App\Models\Status::RNT_REQ_PENDING && $latestMessage && $latestMessage->sender_id === Auth::id();
                        
                        $isPendingReschedule = $latestMessage && $latestMessage->type_id == \App\Models\Status::MSG_RESCHEDULE_PROPOSAL;
                    @endphp

                    <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row gap-6 items-stretch">
                        
                        <div class="w-full md:w-56 lg:w-64 aspect-video md:aspect-auto flex-shrink-0 rounded-2xl overflow-hidden bg-gray-100 relative">
                            <img src="{{ $request->space->cover_photo_url }}" alt="{{ $request->space->name }}" class="absolute inset-0 w-full h-full object-cover">
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
                                    @if($isMyTurn)
                                        <span class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-lg text-xs font-black border border-orange-100 tracking-wide uppercase">Action Required</span>
                                        {{-- Only show standalone Cancel if there is no Counter-Proposal widget blocking it --}}
                                        @if(!$isPendingReschedule)
                                            <button @click.prevent="$dispatch('open-cancel-modal-{{ $request->id }}')" class="text-[10px] font-black uppercase tracking-wider text-red-500 hover:text-red-700 transition">Cancel Application</button>
                                        @endif
                                    @elseif($waitingForOwner)
                                        <span class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-black border border-blue-100 tracking-wide uppercase">Waiting for Owner</span>
                                        <button @click.prevent="$dispatch('open-cancel-modal-{{ $request->id }}')" class="text-[10px] font-black uppercase tracking-wider text-red-500 hover:text-red-700 transition">Cancel Application</button>
                                    @elseif($request->status_id == \App\Models\Status::RNT_REQ_ACCEPTED)
                                        <span class="bg-teal-50 text-teal-600 px-3 py-1.5 rounded-lg text-xs font-black border border-teal-100 tracking-wide uppercase">Accepted</span>
                                    @elseif($request->status_id == \App\Models\Status::RNT_REQ_REJECTED)
                                        <span class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-xs font-black border border-red-100 tracking-wide uppercase">Rejected</span>
                                    @elseif($request->status_id == \App\Models\Status::RNT_REQ_CANCELLED)
                                        <span class="bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-xs font-black border border-gray-200 tracking-wide uppercase">Cancelled</span>
                                    @endif
                                </div>
                            </div>

                            @php
                                $ownerMessage = $request->messages->where('sender_id', '!=', Auth::id())->sortByDesc('created_at')->first();
                            @endphp

                            @if($ownerMessage)
                                @php
                                    $bgClass = 'bg-gray-50 border-gray-200'; $textClass = 'text-gray-700'; $titleClass = 'text-gray-500'; $titleText = 'Message from Owner';
                                    if ($ownerMessage->type_id == \App\Models\Status::MSG_APPROVAL_NOTE) {
                                        $bgClass = 'bg-teal-50 border-teal-100'; $textClass = 'text-teal-900'; $titleClass = 'text-teal-700'; $titleText = 'Approval Note';
                                    } elseif ($ownerMessage->type_id == \App\Models\Status::MSG_DECLINE_REASON) {
                                        $bgClass = 'bg-red-50 border-red-100'; $textClass = 'text-red-900'; $titleClass = 'text-red-700'; $titleText = 'Decline Reason';
                                    } elseif ($ownerMessage->type_id == \App\Models\Status::MSG_RESCHEDULE_PROPOSAL) {
                                        $bgClass = 'bg-blue-50 border-blue-100'; $textClass = 'text-blue-900'; $titleClass = 'text-blue-700'; $titleText = 'Owner Counter-Proposal';
                                    }
                                @endphp

                                <div class="mt-5 {{ $bgClass }} border rounded-3xl p-4 text-sm {{ $textClass }}">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <span class="font-black uppercase tracking-wide {{ $titleClass }} text-[10px]">{{ $titleText }}</span>
                                        <span class="text-[10px] opacity-75">{{ \Carbon\Carbon::parse($ownerMessage->created_at)->diffForHumans() }}</span>
                                    </div>
                                    
                                    <p class="whitespace-pre-line font-medium">{{ $ownerMessage->message }}</p>
                                    
                                    @if($isPendingReschedule && $ownerMessage->reschedule && $isMyTurn)
                                        <div class="mt-4 pt-4 border-t border-blue-200/50">
                                            <div class="grid grid-cols-3 gap-2 mb-4 text-xs font-bold text-blue-900">
                                                <div class="bg-white/50 p-2 rounded-xl text-center border border-blue-100">Visit<br>{{ $ownerMessage->reschedule->proposed_visit_date ? \Carbon\Carbon::parse($ownerMessage->reschedule->proposed_visit_date)->format('M d, Y') : 'N/A' }}</div>
                                                <div class="bg-white/50 p-2 rounded-xl text-center border border-blue-100">Start<br>{{ \Carbon\Carbon::parse($ownerMessage->reschedule->proposed_start_date)->format('M d, Y') }}</div>
                                                <div class="bg-white/50 p-2 rounded-xl text-center border border-blue-100">End<br>{{ \Carbon\Carbon::parse($ownerMessage->reschedule->proposed_end_date)->format('M d, Y') }}</div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                <form method="POST" action="{{ route('rents.reschedule.accept', $request->id) }}">
                                                    @csrf <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2.5 rounded-xl font-black text-sm transition shadow-sm">Accept Dates</button>
                                                </form>
                                                <button @click.prevent="$dispatch('open-counter-modal-{{ $request->id }}')" class="w-full bg-white text-blue-700 border border-blue-200 hover:bg-blue-50 px-3 py-2.5 rounded-xl font-black text-sm transition">Propose New</button>
                                                <form method="POST" action="{{ route('rents.cancel', $request->id) }}" onsubmit="return confirm('Rejecting this counter-proposal will cancel your application. Are you sure?');">
                                                    @csrf <button class="w-full bg-white text-red-600 border border-red-200 hover:bg-red-50 px-3 py-2.5 rounded-xl font-black text-sm transition">Reject & Cancel</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mt-6 py-4 border-t border-gray-50">
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
                        </div>
                    </div>

                    {{-- 1. Cancel Confirmation Modal --}}
                    <div x-data="{ showCancel: false }" @open-cancel-modal-{{ $request->id }}.window="showCancel = true" @keydown.escape.window="showCancel = false" class="relative z-50" x-cloak>
                        <div x-show="showCancel" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                        <div x-show="showCancel" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div @click.away="showCancel = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-8">
                                    <h3 class="text-2xl font-black text-gray-900 mb-2">Cancel Application</h3>
                                    <p class="text-sm text-gray-500 font-medium mb-6">Are you sure you want to completely withdraw your application for <strong>{{ $request->space->name }}</strong>? This action cannot be undone.</p>
                                    <div class="flex gap-3">
                                        <button @click="showCancel = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3 font-bold transition">No, Keep It</button>
                                        <form action="{{ route('rents.cancel', $request->id) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white rounded-xl py-3 font-black transition active:scale-95">Yes, Cancel</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Dynamic Counter-Propose Modal (With Price Math!) --}}
                    @if($isMyTurn)
                    <div x-data="{ 
                            showCounter: false,
                            startDate: '{{ $request->start_date }}', 
                            endDate: '{{ $request->end_date }}',
                            visitDate: '{{ $request->visit_date }}',
                            pricingCode: '{{ strtolower($request->pricing->pricingType->code) }}',
                            pricePerUnit: {{ $request->pricing->price }},
                            get duration() {
                                if(!this.startDate || !this.endDate) return 0;
                                let s = new Date(this.startDate); let e = new Date(this.endDate);
                                let diffTime = e - s;
                                if (diffTime < 0) return 0;
                                let diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                                
                                if(this.pricingCode === 'daily') return diffDays;
                                if(this.pricingCode === 'weekly') return Math.floor(diffDays / 7);
                                if(this.pricingCode === 'monthly') return Math.floor(diffDays / 30);
                                return 0;
                            },
                            get total() { return this.duration * this.pricePerUnit; }
                        }" 
                        @open-counter-modal-{{ $request->id }}.window="showCounter = true" @keydown.escape.window="showCounter = false" class="relative z-50" x-cloak>
                        
                        <div x-show="showCounter" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                        <div x-show="showCounter" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div @click.away="showCounter = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-8">
                                    <h3 class="text-xl font-black text-gray-900 mb-2">Counter-Propose Dates</h3>
                                    <p class="text-xs text-gray-500 font-medium mb-4">Ensure your dates fit the <strong>{{ $request->pricing->pricingType->name }}</strong> package you selected.</p>
                                    
                                    {{-- Live Preview Math Box --}}
                                    <div class="bg-gray-50 p-4 rounded-xl mb-4 border border-gray-200 flex justify-between items-center">
                                        <div>
                                            <p class="text-[10px] uppercase font-black text-gray-400">Calculated Duration</p>
                                            <p class="text-sm font-bold" :class="duration > 0 ? 'text-gray-900' : 'text-red-500'" x-text="duration + ' ' + (pricingCode === 'daily' ? 'Days' : (pricingCode === 'weekly' ? 'Weeks' : 'Months'))"></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] uppercase font-black text-gray-400">New Total Price</p>
                                            <p class="text-lg font-black" :class="duration > 0 ? 'text-teal-600' : 'text-red-500'" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total)"></p>
                                        </div>
                                    </div>

                                    <form action="{{ route('rents.reschedule.propose', $request->id) }}" method="POST">
                                        @csrf
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Visit Date (Optional)</label>
                                                <input type="date" name="new_visit_date" x-model="visitDate" :max="startDate" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Start Date <span class="text-red-500">*</span></label>
                                                <input type="date" name="new_start_date" required x-model="startDate" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">End Date <span class="text-red-500">*</span></label>
                                                <input type="date" name="new_end_date" required x-model="endDate" :min="startDate" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Message</label>
                                            <textarea name="response_note" rows="3" placeholder="Leave a note if you are changing the dates..." class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                        </div>
                                        <div class="mt-6 flex gap-3">
                                            <button type="submit" :disabled="duration === 0" :class="duration === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700 active:scale-95'" class="w-full bg-blue-600 text-white rounded-xl py-3 font-black transition">Send Proposal</button>
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
            {{-- Empty State HTML remains the same --}}
        @endif
    </div>
</x-user-layout>