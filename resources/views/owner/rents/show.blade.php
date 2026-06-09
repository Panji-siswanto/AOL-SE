<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>
    
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data>
        
        @php
            $pendingId = \App\Models\Status::where('code', 'rnt_req_pending')->value('id');
            $acceptedId = \App\Models\Status::where('code', 'rnt_req_accepted')->value('id');
            $rejectedId = \App\Models\Status::where('code', 'rnt_req_rejected')->value('id');
            $cancelledId = \App\Models\Status::where('code', 'rnt_req_cancelled')->value('id');

            // --- UNIFIED TIMELINE LOGIC ---
            $timeline = collect();
            foreach($rentRequest->messages as $msg) {
                $timeline->push((object)['type' => 'message', 'model' => $msg, 'time' => $msg->created_at, 'sender_id' => $msg->sender_id]);
            }
            foreach($rentRequest->reschedules as $resc) {
                $timeline->push((object)['type' => 'reschedule', 'model' => $resc, 'time' => $resc->created_at, 'sender_id' => $resc->sender_id]);
            }
            
            $latestInteraction = $timeline->sortByDesc('time')->first();
            
            // Turn logic
            $isMyTurn = $rentRequest->status_id == $pendingId && $latestInteraction && $latestInteraction->sender_id !== Auth::id();
            $waitingForRenter = $rentRequest->status_id == $pendingId && $latestInteraction && $latestInteraction->sender_id === Auth::id();

            $latestReschedule = $rentRequest->reschedules->sortByDesc('created_at')->first();
            $latestMessage = $rentRequest->messages->where('sender_id', '!=', Auth::id())->sortByDesc('created_at')->first();
            
            $isPendingReschedule = $latestInteraction && $latestInteraction->type === 'reschedule';
        @endphp

        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <a href="{{ route('owner.reservations.index') }}" class="text-sm font-bold text-gray-500 hover:text-teal-600 transition flex items-center gap-2">&larr; Back to Reservations</a>
            <div>
                @if($isMyTurn) <span class="bg-orange-50 text-orange-600 px-4 py-2 rounded-xl text-sm font-black border border-orange-100 uppercase">Action Required</span>
                @elseif($waitingForRenter) <span class="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl text-sm font-black border border-blue-100 uppercase">Waiting for Renter</span>
                @elseif($rentRequest->status_id == $acceptedId) <span class="bg-teal-50 text-teal-600 px-4 py-2 rounded-xl text-sm font-black border border-teal-100 uppercase">Accepted</span>
                @elseif($rentRequest->status_id == $rejectedId) <span class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-sm font-black border border-red-100 uppercase">Rejected</span>
                @elseif($rentRequest->status_id == $cancelledId) <span class="bg-gray-100 text-gray-500 px-4 py-2 rounded-xl text-sm font-black border border-gray-200 uppercase">Cancelled</span>
                @else <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm font-black border border-gray-200 uppercase">{{ $rentRequest->status->name }}</span>
                @endif
            </div>
        </div>

        @if(session('error')) <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 font-bold text-sm">{{ session('error') }}</div> @endif
        @if(session('success')) <div class="mb-6 bg-teal-50 text-teal-700 p-4 rounded-xl border border-teal-100 font-bold text-sm">{{ session('success') }}</div> @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-12 items-start">
            <div class="lg:col-span-2 space-y-8">
                
                <div class="bg-white p-6 sm:p-8 rounded-[2rem] border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 items-start">
                    <div class="md:col-span-1 w-full aspect-video rounded-xl overflow-hidden bg-gray-100 relative">
                        <img src="{{ $rentRequest->space->cover_photo_url }}" class="absolute inset-0 w-full h-full object-cover">
                    </div>
                    <div class="md:col-span-2 w-full min-w-0"> 
                        <h2 class="text-2xl font-black text-gray-900 mb-3">{{ $rentRequest->space->name }}</h2>
                        <div class="space-y-2 text-sm font-medium text-gray-600">
                            <p class="flex items-start gap-2"><span class="text-teal-600 mt-0.5">📍</span> <span class="truncate">{{ $rentRequest->space->location->address }}, {{ $rentRequest->space->location->city }}</span></p>
                            <p class="flex items-center gap-2"><span class="text-teal-600">📏</span> {{ $rentRequest->space->formatted_size }}</p>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Applicant</p>
                                <p class="font-black text-gray-900 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs">{{ substr($rentRequest->renter->name, 0, 1) }}</span> {{ $rentRequest->renter->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clean Message Box --}}
                @if($latestMessage)
                    <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2"><span>💬</span> Latest Message</h3>
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-[10px] font-black uppercase tracking-wider text-blue-600">From: Renter</span>
                                <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($latestMessage->created_at)->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-700 font-medium leading-relaxed whitespace-pre-line">{{ $latestMessage->message }}</p>
                        </div>
                    </div>
                @endif

                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2"><span>📅</span> Active Contract Dates</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        @if($rentRequest->visit_date)
                            <div class="p-5 bg-teal-50 rounded-2xl border border-teal-200">
                                <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 block mb-1">Scheduled Visit</span>
                                <span class="text-lg font-black text-teal-900">{{ \Carbon\Carbon::parse($rentRequest->visit_date)->format('M d, Y') }}</span>
                            </div>
                        @else
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 opacity-50">
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">Scheduled Visit</span>
                                <span class="text-sm font-black text-gray-500 mt-1 block">Not Scheduled</span>
                            </div>
                        @endif
                        <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">Contract Start</span>
                            <span class="text-lg font-black text-gray-900">{{ \Carbon\Carbon::parse($rentRequest->start_date)->format('M d, Y') }}</span>
                        </div>
                        <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">Contract End</span>
                            <span class="text-lg font-black text-gray-900">{{ \Carbon\Carbon::parse($rentRequest->end_date)->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 sticky top-28 space-y-6">
                <div class="bg-white p-6 sm:p-8 rounded-[2rem] border border-gray-200 shadow-xl shadow-gray-100/50">
                    <h3 class="text-sm font-black uppercase tracking-wider text-gray-400 mb-6 border-b border-gray-100 pb-4">Financial Summary</h3>
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-500">Agreed Rate ({{ $rentRequest->pricing->pricingType->name }})</span>
                            <span class="text-sm font-black text-gray-900">Rp {{ number_format($rentRequest->pricing->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                            <span class="text-lg font-black text-gray-900">Total Revenue</span>
                            <span class="text-2xl font-black text-teal-600">Rp {{ number_format($rentRequest->total_price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($isMyTurn)
                        <div class="space-y-3 pt-6 border-t border-gray-100">
                            <button @click.prevent="$dispatch('open-approve-modal')" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-2xl font-black transition-all active:scale-95 shadow-lg shadow-teal-600/30">Accept Application</button>
                            <button @click.prevent="$dispatch('open-reschedule-modal')" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 py-3.5 rounded-2xl font-black transition-all active:scale-95 shadow-sm">Propose New Dates</button>
                            <button @click.prevent="$dispatch('open-decline-modal')" class="w-full bg-white hover:bg-red-50 text-red-500 hover:text-red-600 border-2 border-red-100 hover:border-red-200 py-3.5 rounded-2xl font-black transition-all active:scale-95">Decline Application</button>
                        </div>
                    @elseif($waitingForRenter)
                        <div class="pt-6 border-t border-gray-100 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-blue-500 mb-3">
                                <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h4 class="font-black text-gray-900 text-sm">Waiting for Renter</h4>
                            <p class="text-xs text-gray-500 mt-1 mb-4">You have proposed new dates.</p>
                            <button @click.prevent="$dispatch('open-decline-modal')" class="text-xs font-bold text-red-500 hover:text-red-700 underline">Withdraw & Decline</button>
                        </div>
                    @endif
                </div>

                @if($isMyTurn && $isPendingReschedule && $latestReschedule)
                    <div class="bg-blue-50 border border-blue-200 rounded-[2rem] p-6 shadow-sm">
                        <h4 class="font-black text-blue-900 mb-2 uppercase text-xs tracking-wider flex items-center gap-2"><span class="text-base">🔔</span> Renter Counter-Proposal</h4>
                        
                        @if($latestMessage && $latestMessage->created_at->diffInMinutes($latestReschedule->created_at) < 5)
                            <p class="text-sm font-medium text-blue-800 mb-4 whitespace-pre-line">{{ $latestMessage->message }}</p>
                        @else
                            <p class="text-sm font-medium text-blue-800/60 italic mb-4">Dates proposed without additional comments.</p>
                        @endif
                        
                        <div class="grid grid-cols-3 gap-2 mb-5 text-xs font-bold text-blue-900">
                            <div class="bg-white/60 border border-blue-100 p-2 rounded-xl text-center">Visit<br>{{ $latestReschedule->proposed_visit_date ? \Carbon\Carbon::parse($latestReschedule->proposed_visit_date)->format('M d') : 'N/A' }}</div>
                            <div class="bg-white/60 border border-blue-100 p-2 rounded-xl text-center">Start<br>{{ \Carbon\Carbon::parse($latestReschedule->proposed_start_date)->format('M d') }}</div>
                            <div class="bg-white/60 border border-blue-100 p-2 rounded-xl text-center">End<br>{{ \Carbon\Carbon::parse($latestReschedule->proposed_end_date)->format('M d') }}</div>
                        </div>

                        <div class="flex gap-2">
                            <button @click.prevent="$dispatch('open-approve-modal')" class="w-full bg-blue-600 text-white rounded-xl py-2.5 font-black text-sm hover:bg-blue-700 transition active:scale-95 shadow-sm">Accept Dates</button>
                            <button @click.prevent="$dispatch('open-decline-modal')" class="w-full bg-white text-red-600 border border-red-200 rounded-xl py-2.5 font-black text-sm hover:bg-red-50 transition active:scale-95">Decline</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- MODALS SECTION --}}
        @if($rentRequest->status_id == $pendingId)
            
            {{-- 1. Approve Modal --}}
            <div x-data="{ showApprove: false, showNote: false }" @open-approve-modal.window="showApprove = true" @keydown.escape.window="showApprove = false" class="relative z-50" x-cloak>
                <div x-show="showApprove" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                <div x-show="showApprove" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div @click.away="showApprove = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-8">
                            <h3 class="text-2xl font-black text-gray-900 mb-2">Accept Application</h3>
                            <p class="text-sm text-gray-500 font-medium mb-4">You are about to accept the application from {{ $rentRequest->renter->name }}.</p>
                            
                            <form action="{{ route('owner.reservations.approve', $rentRequest->id) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="mb-6">
                                    <button x-show="!showNote" @click="showNote = true" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-50 text-teal-700 font-bold text-sm rounded-xl hover:bg-teal-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg> Add a message (Optional)
                                    </button>
                                    <div x-show="showNote" x-transition style="display: none;">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Your Message</label>
                                        <textarea x-ref="noteInput" name="response_note" rows="3" class="w-full rounded-2xl border-gray-300 focus:border-teal-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                        <button @click="showNote = false; $refs.noteInput.value = ''" type="button" class="mt-2 text-xs font-bold text-red-500 hover:text-red-700">Cancel message</button>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white rounded-xl py-3 font-black transition active:scale-95 shadow-sm">Accept & Finalize</button>
                                    <button type="button" @click="showApprove = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3 font-bold transition">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Reschedule Modal --}}
            <div x-data="{ 
                    showReschedule: false, showNote: false,
                    startDate: '{{ $rentRequest->start_date }}', endDate: '{{ $rentRequest->end_date }}', visitDate: '{{ $rentRequest->visit_date }}',
                    pricingCode: '{{ strtolower($rentRequest->pricing->pricingType->code) }}', pricePerUnit: {{ $rentRequest->pricing->price }},
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
                @open-reschedule-modal.window="showReschedule = true" @keydown.escape.window="showReschedule = false" class="relative z-50" x-cloak>
                <div x-show="showReschedule" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                <div x-show="showReschedule" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div @click.away="showReschedule = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-8">
                            <h3 class="text-xl font-black text-gray-900 mb-2">Propose Alternative Dates</h3>
                            
                            <div class="bg-blue-50/50 p-4 rounded-xl mb-4 border border-blue-100 flex justify-between items-center mt-4">
                                <div><p class="text-[10px] uppercase font-black text-blue-400">Total Duration</p><p class="text-sm font-black text-blue-900" x-text="durationText"></p></div>
                                <div class="text-right"><p class="text-[10px] uppercase font-black text-blue-400">Prorated Total Price</p><p class="text-lg font-black text-teal-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total)"></p></div>
                            </div>

                            <form action="{{ route('owner.reservations.reschedule', $rentRequest->id) }}" method="POST">
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
                                <div class="mb-6">
                                    <button x-show="!showNote" @click="showNote = true" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 font-bold text-sm rounded-xl hover:bg-blue-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg> Add a message (Optional)
                                    </button>
                                    <div x-show="showNote" x-transition style="display: none;">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Your Message</label>
                                        <textarea x-ref="noteInput" name="response_note" rows="3" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                        <button @click="showNote = false; $refs.noteInput.value = ''" type="button" class="mt-2 text-xs font-bold text-red-500 hover:text-red-700">Cancel message</button>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit" :disabled="total === 0" :class="total === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700 active:scale-95'" class="w-full bg-blue-600 text-white rounded-xl py-3 font-black transition">Send Proposal</button>
                                    <button type="button" @click="showReschedule = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3 font-bold transition">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Decline Modal --}}
            <div x-data="{ showDecline: false, showNote: false }" @open-decline-modal.window="showDecline = true" @keydown.escape.window="showDecline = false" class="relative z-50" x-cloak>
                <div x-show="showDecline" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                <div x-show="showDecline" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div @click.away="showDecline = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-8">
                            <h3 class="text-2xl font-black text-gray-900 mb-2">Decline Application</h3>
                            <p class="text-sm text-gray-500 font-medium mb-4">Are you sure you want to decline this application? This action cannot be undone.</p>
                            
                            <form action="{{ route('owner.reservations.reject', $rentRequest->id) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="mb-6">
                                    <button x-show="!showNote" @click="showNote = true" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 font-bold text-sm rounded-xl hover:bg-red-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg> Add a decline reason (Optional)
                                    </button>
                                    <div x-show="showNote" x-transition style="display: none;">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Decline Reason</label>
                                        <textarea x-ref="noteInput" name="reject_reason" rows="3" class="w-full rounded-2xl border-gray-300 focus:border-red-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                        <button @click="showNote = false; $refs.noteInput.value = ''" type="button" class="mt-2 text-xs font-bold text-red-500 hover:text-red-700">Cancel message</button>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white rounded-xl py-3 font-black transition active:scale-95 shadow-sm">Decline</button>
                                    <button type="button" @click="showDecline = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3 font-bold transition">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-user-layout>