<x-user-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data>
        <style> [x-cloak] { display: none !important; } </style>
        
        <div class="mb-8">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Incoming Reservations</h1>
            <p class="text-gray-500 font-medium mt-2">Manage applications and ongoing rentals for your properties.</p>
        </div>

        @if(session('success')) <div class="mb-6 bg-teal-50 text-teal-700 p-4 rounded-xl border border-teal-100 font-bold text-sm shadow-sm">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 font-bold text-sm shadow-sm">{{ session('error') }}</div> @endif

        @php
            $currentStatus = request('status', 'all');
            $currentSort = request('sort', 'latest');
        @endphp

        <div class="mb-8">
            <div class="flex space-x-1 bg-gray-100/80 p-1 rounded-2xl w-full sm:w-fit mb-6 overflow-x-auto scrollbar-hide">
                <a href="{{ route('owner.reservations.index', ['status' => 'all', 'sort' => $currentSort, 'search' => request('search')]) }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap {{ $currentStatus === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">All</a>
                <a href="{{ route('owner.reservations.index', ['status' => 'action_required', 'sort' => $currentSort, 'search' => request('search')]) }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap {{ $currentStatus === 'action_required' ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">Action Required</a>
                <a href="{{ route('owner.reservations.index', ['status' => 'rnt_req_pending', 'sort' => $currentSort, 'search' => request('search')]) }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap {{ $currentStatus === 'rnt_req_pending' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">Pending</a>
                <a href="{{ route('owner.reservations.index', ['status' => 'rnt_awaiting_payment', 'sort' => $currentSort, 'search' => request('search')]) }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap {{ $currentStatus === 'rnt_awaiting_payment' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">Waiting for Payment</a>
                <a href="{{ route('owner.reservations.index', ['status' => 'rnt_ongoing', 'sort' => $currentSort, 'search' => request('search')]) }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap {{ $currentStatus === 'rnt_ongoing' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">Ongoing</a>
                <a href="{{ route('owner.reservations.index', ['status' => 'rnt_completed', 'sort' => $currentSort, 'search' => request('search')]) }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap {{ $currentStatus === 'rnt_completed' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">Completed</a>
                <a href="{{ route('owner.reservations.index', ['status' => 'rnt_req_cancelled', 'sort' => $currentSort, 'search' => request('search')]) }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap {{ $currentStatus === 'rnt_req_cancelled' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">Cancelled</a>
                <a href="{{ route('owner.reservations.index', ['status' => 'rnt_req_rejected', 'sort' => $currentSort, 'search' => request('search')]) }}" class="px-6 py-2.5 text-sm font-bold rounded-xl transition-all whitespace-nowrap {{ $currentStatus === 'rnt_req_rejected' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">Rejected</a>
            </div>

            <form method="GET" action="{{ route('owner.reservations.index') }}" class="flex flex-col md:flex-row gap-4 items-center">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                
                <div class="flex-1 relative w-full">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by space name or renter name..." class="w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 focus:border-teal-500 focus:ring-teal-500 text-sm font-medium shadow-sm">
                </div>
                
                <div class="flex gap-3 w-full md:w-auto">
                    <select name="sort" onchange="this.form.submit()" class="w-full sm:w-auto rounded-xl border-gray-200 focus:border-teal-500 focus:ring-teal-500 text-sm font-bold text-gray-700 py-3 pl-4 pr-10 shadow-sm cursor-pointer">
                        <option value="latest" {{ $currentSort == 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ $currentSort == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="price_high" {{ $currentSort == 'price_high' ? 'selected' : '' }}>Revenue: High to Low</option>
                        <option value="price_low" {{ $currentSort == 'price_low' ? 'selected' : '' }}>Revenue: Low to High</option>
                    </select>
                    <button type="submit" class="bg-gray-900 hover:bg-black text-white px-6 py-3 rounded-xl text-sm font-black transition active:scale-95 shadow-sm">Search</button>
                    
                    @if(request()->hasAny(['search', 'sort', 'status']) && (request('search') != '' || request('sort') != 'latest' || request('status') != 'all'))
                        <a href="{{ route('owner.reservations.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-3 rounded-xl text-sm font-black transition active:scale-95 shadow-sm flex items-center justify-center" title="Clear Filters">&times;</a>
                    @endif
                </div>
            </form>
        </div>

        @if($requests->count() > 0)
            <div class="space-y-4">
                @foreach($requests as $request)
                    @php
                        $pendingId = \App\Models\Status::where('code', 'rnt_req_pending')->value('id');
                        $rejectedId = \App\Models\Status::where('code', 'rnt_req_rejected')->value('id');
                        $cancelledId = \App\Models\Status::where('code', 'rnt_req_cancelled')->value('id');
                        $awaitingPaymentId = \App\Models\Status::where('code', 'rnt_awaiting_payment')->value('id');
                        $ongoingId = \App\Models\Status::where('code', 'rnt_ongoing')->value('id');
                        $completedId = \App\Models\Status::where('code', 'rnt_completed')->value('id');
                        $msgFinishReqId = \App\Models\Status::where('code', 'msg_finish_request')->value('id');
                        $msgFinishRejectedId = \App\Models\Status::where('code', 'msg_finish_rejected')->value('id');

                        $latestReschedule = $request->reschedules->sortByDesc('created_at')->first();
                        $latestMessageAll = $request->messages->sortByDesc('created_at')->first();

                        $latestSenderId = $request->renter_id;
                        $latestTime = $request->created_at;
                        $actionType = 0;

                        if ($latestMessageAll && $latestMessageAll->created_at->gte($latestTime)) {
                            $latestSenderId = $latestMessageAll->sender_id;
                            $latestTime = $latestMessageAll->created_at;
                            $actionType = 1;
                        }

                        if ($latestReschedule && $latestReschedule->created_at->gte($latestTime)) {
                            if ($latestReschedule->created_at->gt($latestTime) || $actionType <= 2) {
                                $latestSenderId = $latestReschedule->sender_id;
                                $latestTime = $latestReschedule->created_at;
                            }
                        }

                        $isPendingReschedule = ($request->status_id == $pendingId && $latestReschedule) ? true : false;
                        $isMyTurn = $request->status_id == $pendingId && $latestSenderId !== Auth::id();
                        $waitingForRenter = $request->status_id == $pendingId && $latestSenderId === Auth::id();

                        $pendingFinishMsg = null;
                        $rejectedFinishMsg = null;
                        $isMyTurnFinish = false;
                        $waitingForRenterFinish = false;

                        if ($request->status_id == $ongoingId) {
                            $latestFinishStatusMsg = $request->messages->whereIn('type_id', [
                                $msgFinishReqId, 
                                \App\Models\Status::where('code', 'msg_finish_accepted')->value('id'), 
                                $msgFinishRejectedId
                            ])->sortByDesc('id')->first();

                            if ($latestFinishStatusMsg) {
                                if ($latestFinishStatusMsg->type_id == $msgFinishReqId) {
                                    $pendingFinishMsg = $latestFinishStatusMsg;
                                    $isMyTurnFinish = $pendingFinishMsg->sender_id !== Auth::id();
                                    $waitingForRenterFinish = $pendingFinishMsg->sender_id === Auth::id();
                                } elseif ($latestFinishStatusMsg->type_id == $msgFinishRejectedId) {
                                    $rejectedFinishMsg = $latestFinishStatusMsg; 
                                }
                            }
                        }

                        $rates = \App\Models\SpaceRegistrationPrice::where('space_registration_id', $request->space->registration_id)
                            ->join('pricing_types', 'space_registration_prices.pricing_type_id', '=', 'pricing_types.id')
                            ->pluck('space_registration_prices.price', 'pricing_types.code')
                            ->mapWithKeys(fn($item, $key) => [strtolower($key) => $item]);

                        $dailyRate = $rates['daily'] ?? 'null';
                        $weeklyRate = $rates['weekly'] ?? 'null';
                        $monthlyRate = $rates['monthly'] ?? 'null';

                        $msgApproveId = \App\Models\Status::where('code', 'msg_approval_note')->value('id');
                        $msgDeclineId = \App\Models\Status::where('code', 'msg_decline_reason')->value('id');
                        $msgRescheduleId = \App\Models\Status::where('code', 'msg_reschedule_proposal')->value('id');
                        
                        $propStart = $isPendingReschedule ? $latestReschedule->proposed_start_date : $request->start_date;
                        $propEnd = $isPendingReschedule ? $latestReschedule->proposed_end_date : $request->end_date;
                        $propVisit = $isPendingReschedule ? $latestReschedule->proposed_visit_date : $request->visit_date;

                        $startDateFormatted = $propStart ? \Carbon\Carbon::parse($propStart)->format('Y-m-d') : '';
                        $endDateFormatted = $propEnd ? \Carbon\Carbon::parse($propEnd)->format('Y-m-d') : '';
                        $visitDateFormatted = $propVisit ? \Carbon\Carbon::parse($propVisit)->format('Y-m-d') : '';

                        $displayPrice = $isPendingReschedule ? $latestReschedule->proposed_total_price : $request->total_price;
                        $displayBreakdown = $isPendingReschedule ? $latestReschedule->price_breakdown : $request->price_breakdown;

                        $durationParts = [];
                        if($displayBreakdown && is_array($displayBreakdown)) {
                            if(isset($displayBreakdown['monthly'])) $durationParts[] = $displayBreakdown['monthly']['qty'] . ' Mo';
                            if(isset($displayBreakdown['weekly'])) $durationParts[] = $displayBreakdown['weekly']['qty'] . ' Wk';
                            if(isset($displayBreakdown['daily'])) $durationParts[] = $displayBreakdown['daily']['qty'] . ' Day';
                            if(isset($displayBreakdown['prorated_days'])) $durationParts[] = $displayBreakdown['prorated_days']['qty'] . ' Day';
                        }
                        $durationString = !empty($durationParts) ? implode(', ', $durationParts) : 'N/A';
                    @endphp

                    {{-- MAIN COMPACT CARD --}}
                    <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row gap-6 items-center">
                        <div class="w-full md:w-48 h-32 flex-shrink-0 rounded-2xl overflow-hidden bg-gray-100 relative">
                            <img src="{{ $request->space->cover_photo_url }}" class="absolute inset-0 w-full h-full object-cover">
                        </div>

                        <div class="flex-1 min-w-0 w-full flex flex-col justify-between h-full">
                            <div class="flex justify-between items-start gap-4 mb-3">
                                <div class="min-w-0">
                                    <h3 class="text-lg font-black text-gray-900 truncate">{{ $request->space->name }}</h3>
                                    <p class="text-[10px] font-bold text-gray-500 mt-1 truncate">
                                        <span class="bg-gray-100 px-2 py-1 rounded-md text-gray-700 uppercase">Applicant: {{ $request->renter->name }}</span>
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    {{-- Dynamic Action Required Badges --}}
                                    @if($isMyTurnFinish)
                                        <span class="bg-orange-500 text-white px-3 py-1.5 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-wider">Action Required: Finish Request</span>
                                    @elseif($isMyTurn && $isPendingReschedule)
                                        <span class="bg-orange-500 text-white px-3 py-1.5 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-wider">Action Required: Reschedule</span>
                                    @elseif($isMyTurn && !$isPendingReschedule)
                                        <span class="bg-orange-500 text-white px-3 py-1.5 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-wider">Action Required: New Application</span>
                                    
                                    {{-- General Status Badges --}}
                                    @elseif($request->status_id == $awaitingPaymentId) 
                                        <span class="bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg text-[10px] font-black border border-indigo-100 uppercase tracking-wider">Waiting for Payment</span>
                                    @elseif($waitingForRenter || $waitingForRenterFinish) 
                                        <span class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-[10px] font-black border border-blue-100 uppercase tracking-wider">Waiting for Renter</span>
                                    @elseif($request->status_id == $ongoingId) 
                                        <span class="bg-blue-500 text-white px-3 py-1.5 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-wider">Ongoing</span>
                                    @elseif($request->status_id == $completedId) 
                                        <span class="bg-gray-800 text-white px-3 py-1.5 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-wider">Completed</span>
                                    @elseif($request->status_id == $rejectedId) 
                                        <span class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black border border-red-100 uppercase tracking-wider">Rejected</span>
                                    @elseif($request->status_id == $cancelledId) 
                                        <span class="bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-[10px] font-black border border-gray-200 uppercase tracking-wider">Cancelled</span>
                                    @else 
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg text-[10px] font-black border border-gray-200 uppercase tracking-wider">{{ $request->status->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50/80 rounded-xl p-3 border border-gray-100/80 mb-3">
                                <div><p class="text-[9px] font-black uppercase text-gray-400 mb-0.5">Revenue</p><p class="text-xs font-bold text-orange-500">Rp {{ number_format($displayPrice, 0, ',', '.') }}</p></div>
                                <div><p class="text-[9px] font-black uppercase text-gray-400 mb-0.5">Duration</p><p class="text-xs font-bold text-gray-900 truncate">{{ $durationString }}</p></div>
                                <div><p class="text-[9px] font-black uppercase text-gray-400 mb-0.5">Start</p><p class="text-xs font-bold text-gray-900">{{ $propStart ? \Carbon\Carbon::parse($propStart)->format('M d') : 'N/A' }}</p></div>
                                <div><p class="text-[9px] font-black uppercase text-gray-400 mb-0.5">End</p><p class="text-xs font-bold text-gray-900">{{ $propEnd ? \Carbon\Carbon::parse($propEnd)->format('M d') : 'N/A' }}</p></div>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="text-[10px] font-bold text-gray-400 flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Updated {{ $request->updated_at->diffForHumans() }}
                                </div>
                                <button @click.prevent="$dispatch('open-details-modal-{{ $request->id }}')" class="{{ ($isMyTurn || $isMyTurnFinish) ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'bg-gray-900 hover:bg-black text-white' }} px-5 py-2 rounded-xl text-xs font-black transition-all shadow-md active:scale-95">
                                    {{ ($isMyTurn || $isMyTurnFinish) ? 'Review & Respond' : 'View Details' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 1. MASTER DETAILS MODAL (Owner Perspective) --}}
                    <div x-data="{ showDetails: false }" @open-details-modal-{{ $request->id }}.window="showDetails = true" @keydown.escape.window="showDetails = false" class="relative z-40" x-cloak>
                        <div x-show="showDetails" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                        <div x-show="showDetails" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div class="flex min-h-full items-center justify-center p-4 text-center sm:items-center sm:p-0">
                                <div @click.away="showDetails = false" class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-100 p-8 sm:p-12">
                                    
                                    {{-- Header --}}
                                    <div class="flex justify-between items-center mb-8">
                                        <div>
                                            <h3 class="text-3xl font-black text-gray-900 tracking-tight">Request Details</h3>
                                            <p class="text-xs font-bold text-gray-500 mt-1 uppercase tracking-wider">Applicant: {{ $request->renter->name }}</p>
                                        </div>
                                        <button @click="showDetails = false" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 p-2 rounded-full transition">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-12">
                                        
                                        {{-- LEFT COLUMN: Clean Information Grid --}}
                                        <div class="lg:col-span-3 space-y-8">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100 col-span-2 sm:col-span-1">
                                                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Total Revenue</p>
                                                    <p class="text-2xl font-black text-orange-500">Rp {{ number_format($displayPrice, 0, ',', '.') }}</p>
                                                </div>
                                                <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100 col-span-2 sm:col-span-1">
                                                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Duration</p>
                                                    <p class="text-lg font-black text-gray-900 mt-1">{{ $durationString }}</p>
                                                </div>
                                                <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100">
                                                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Start Date</p>
                                                    <p class="text-sm font-bold text-gray-900 mt-1">{{ $propStart ? \Carbon\Carbon::parse($propStart)->format('M d, Y') : 'N/A' }}</p>
                                                </div>
                                                <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100">
                                                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">End Date</p>
                                                    <p class="text-sm font-bold text-gray-900 mt-1">{{ $propEnd ? \Carbon\Carbon::parse($propEnd)->format('M d, Y') : 'N/A' }}</p>
                                                </div>
                                                @if($request->visit_date || ($isPendingReschedule && $latestReschedule->proposed_visit_date))
                                                    <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100 col-span-2">
                                                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Scheduled Visit Date</p>
                                                        <p class="text-sm font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($propVisit)->format('M d, Y') }}</p>
                                                    </div>
                                                @endif
                                            </div>

                                            @if($displayBreakdown && is_array($displayBreakdown))
                                                <div class="px-4 border-l-2 border-gray-200">
                                                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-wider mb-2">Revenue Breakdown Calculation</p>
                                                    <div class="text-xs text-gray-500 font-bold flex flex-wrap gap-3">
                                                        @if(isset($displayBreakdown['monthly'])) <span>{{ $displayBreakdown['monthly']['qty'] }} Month(s)</span> @endif
                                                        @if(isset($displayBreakdown['weekly'])) <span>{{ $displayBreakdown['weekly']['qty'] }} Week(s)</span> @endif
                                                        @if(isset($displayBreakdown['daily'])) <span>{{ $displayBreakdown['daily']['qty'] }} Day(s)</span> @endif
                                                        @if(isset($displayBreakdown['prorated_days'])) <span>{{ $displayBreakdown['prorated_days']['qty'] }} Prorated Day(s)</span> @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- RIGHT COLUMN: Context Messages & Actions --}}
                                        <div class="lg:col-span-2 flex flex-col h-full justify-between border-t lg:border-t-0 lg:border-l border-gray-100 pt-8 lg:pt-0 lg:pl-10">
                                            
                                            <div class="flex-grow flex flex-col">

                                                {{-- QUARANTINED: AWAITING RENTER PAYMENT --}}
                                                @if($request->status_id == $awaitingPaymentId)
                                                    <div class="mb-8 bg-orange-50/50 border border-orange-100 rounded-3xl p-6 text-center">
                                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-orange-100 text-orange-600 mb-3 shadow-inner">
                                                            <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        </div>
                                                        <h4 class="text-sm font-black text-orange-900 mb-1 uppercase tracking-wider">Awaiting Payment</h4>
                                                        <p class="text-xs font-medium text-orange-700">You have approved this request. The contract will automatically activate once {{ $request->renter->name }} settles the payment.</p>
                                                    </div>
                                                
                                                {{-- ONGOING & EARLY FINISH CONTEXT --}}
                                                @elseif($request->status_id == $ongoingId || $request->status_id == $completedId)
                                                    @if($pendingFinishMsg)
                                                        <div class="mb-8 border-2 border-orange-100 bg-orange-50/50 rounded-3xl p-6 relative">
                                                            <div class="flex items-center gap-2 mb-3">
                                                                <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                                                <span class="text-[10px] font-black uppercase text-orange-600 tracking-wider">
                                                                    {{ $pendingFinishMsg->sender_id == Auth::id() ? 'Your Early Finish Request' : "Renter's Early Finish Request" }}
                                                                </span>
                                                            </div>
                                                            <p class="text-sm font-medium text-gray-700 leading-relaxed">{{ $pendingFinishMsg->message }}</p>
                                                        </div>

                                                    {{-- DYNAMIC REJECTION MESSAGE --}}
                                                    @elseif($rejectedFinishMsg)
                                                        <div class="mb-8 border-2 {{ $rejectedFinishMsg->sender_id == Auth::id() ? 'border-blue-100 bg-blue-50/50' : 'border-red-100 bg-red-50/50' }} rounded-3xl p-6 relative">
                                                            <div class="flex items-center gap-2 mb-3">
                                                                @if($rejectedFinishMsg->sender_id == Auth::id())
                                                                    <span class="text-[10px] font-black uppercase text-blue-600 tracking-wider">Your Rejection Note</span>
                                                                @else
                                                                    <span class="text-base">❌</span>
                                                                    <span class="text-[10px] font-black uppercase text-red-600 tracking-wider">Renter's Rejection Reason</span>
                                                                @endif
                                                            </div>
                                                            <p class="text-sm font-medium {{ $rejectedFinishMsg->sender_id == Auth::id() ? 'text-blue-900' : 'text-red-900' }} leading-relaxed">{{ $rejectedFinishMsg->message }}</p>
                                                        </div>

                                                    @elseif($request->status_id == $ongoingId)
                                                        <div class="mb-8 bg-blue-50/50 border border-blue-100 rounded-3xl p-6">
                                                            <span class="text-[10px] font-black uppercase text-blue-700 block mb-2">Rent Active</span>
                                                            <p class="text-sm font-medium text-blue-900 leading-relaxed">This contract is currently ongoing until {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}.</p>
                                                        </div>
                                                    @endif
                                                @endif

                                                {{-- PENDING/RESCHEDULE CONTEXT --}}
                                                @if($isPendingReschedule && $latestReschedule)
                                                    @php $rescheduleMsg = $request->messages->where('sender_id', $latestSenderId)->where('created_at', '>=', $latestReschedule->created_at->subMinutes(2))->first(); @endphp
                                                    <div class="mb-8 border-2 border-orange-100 rounded-3xl p-6 relative">
                                                        <div class="flex items-center gap-2 mb-3">
                                                            <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                                            <span class="text-[10px] font-black uppercase text-orange-600 tracking-wider">
                                                                {{ $isMyTurn ? "Renter's Proposal Alternative" : 'Your Counter-Proposal' }}
                                                            </span>
                                                        </div>
                                                        <p class="text-sm font-medium text-gray-800 border-l-2 border-orange-200 pl-3 py-1">{{ $rescheduleMsg ? $rescheduleMsg->message : 'Alternative dates proposed.' }}</p>
                                                    </div>
                                                @elseif(in_array($request->status_id, [$rejectedId, $cancelledId]) && $declineMsg = $request->messages->where('type_id', $msgDeclineId)->sortByDesc('created_at')->first())
                                                    <div class="mb-8 bg-red-50/50 border border-red-100 rounded-3xl p-6">
                                                        <span class="text-[10px] font-black uppercase text-red-700 block mb-2">Your Decline Reason:</span>
                                                        <p class="text-sm font-medium text-red-900 leading-relaxed">{{ $declineMsg->message }}</p>
                                                    </div>
                                                @elseif($request->status_id == $pendingId && !$isPendingReschedule)
                                                    @php $appMsg = $request->messages->where('sender_id', $request->renter_id)->whereNotIn('type_id', [$msgApproveId, $msgDeclineId])->sortByDesc('created_at')->first(); @endphp
                                                    @if($appMsg)
                                                        <div class="mb-8">
                                                            <span class="text-[10px] font-black uppercase text-gray-500 tracking-wider flex items-center gap-2 mb-3"><span>💬</span> Renter's Proposal</span>
                                                            <div class="bg-gray-50 p-5 rounded-3xl border border-gray-100">
                                                                <p class="text-sm font-medium text-gray-800 leading-relaxed">{{ $appMsg->message }}</p>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="mt-4 text-center p-8 bg-gray-50 rounded-3xl border border-gray-100 border-dashed">
                                                            <span class="text-4xl block mb-4">📥</span>
                                                            <p class="text-sm font-bold text-gray-900 mb-1">No attached message</p>
                                                            <p class="text-xs font-medium text-gray-500">The applicant sent this request without adding a custom proposal message.</p>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>

                                            {{-- ACTION BUTTONS --}}
                                            <div class="space-y-3 mt-4">
                                                
                                                {{-- Early Finish Actions --}}
                                                @if($request->status_id == $ongoingId)
                                                    @if($isMyTurnFinish)
                                                        <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-accept-finish-modal-{{ $request->id }}'), 150)" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3.5 rounded-2xl font-black text-sm transition shadow-lg active:scale-95">Accept Early Finish</button>
                                                        <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-reject-finish-modal-{{ $request->id }}'), 150)" class="w-full bg-white text-red-500 border border-red-100 py-3.5 rounded-2xl font-black text-sm transition mt-2">Reject Request</button>
                                                    @elseif($waitingForRenterFinish)
                                                        <button class="w-full bg-gray-100 text-gray-500 py-3.5 rounded-2xl font-bold text-sm cursor-not-allowed" disabled>Waiting for Renter Response...</button>
                                                    @else
                                                        <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-request-finish-modal-{{ $request->id }}'), 150)" class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-3.5 rounded-2xl font-black text-sm transition active:scale-95">Request Early Finish</button>
                                                        <button @click="showDetails = false" class="w-full bg-gray-900 hover:bg-black text-white py-3.5 rounded-2xl font-black text-sm transition mt-2">Close</button>
                                                    @endif
                                                
                                                {{-- Booking Negotiation Actions --}}
                                                @elseif($isMyTurn && $isPendingReschedule)
                                                    <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-approve-modal-{{ $request->id }}'), 150)" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-2xl font-black text-sm transition shadow-lg shadow-teal-600/20 active:scale-95">Accept Application</button>
                                                    <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-reschedule-modal-{{ $request->id }}'), 150)" class="w-full bg-white text-blue-700 border-2 border-blue-200 hover:border-blue-300 hover:bg-blue-50 py-3.5 rounded-2xl font-black text-sm transition shadow-sm active:scale-95">Propose New Dates</button>
                                                    <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-decline-modal-{{ $request->id }}'), 150)" class="w-full bg-white text-red-500 border border-red-100 hover:bg-red-50 py-3.5 rounded-2xl font-black text-sm transition active:scale-95 mt-2">Decline Application</button>
                                                @elseif($isMyTurn && !$isPendingReschedule)
                                                    <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-approve-modal-{{ $request->id }}'), 150)" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-2xl font-black text-sm transition shadow-lg shadow-teal-600/20 active:scale-95">Accept Application</button>
                                                    <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-reschedule-modal-{{ $request->id }}'), 150)" class="w-full bg-white text-blue-700 border-2 border-blue-200 hover:border-blue-300 hover:bg-blue-50 py-3.5 rounded-2xl font-black text-sm transition shadow-sm active:scale-95">Propose Dates</button>
                                                    <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-decline-modal-{{ $request->id }}'), 150)" class="w-full bg-white text-red-500 border border-red-100 hover:bg-red-50 py-3.5 rounded-2xl font-black text-sm transition active:scale-95 mt-2">Decline</button>
                                                @elseif($waitingForRenter)
                                                    <button @click.prevent="showDetails = false; setTimeout(() => $dispatch('open-decline-modal-{{ $request->id }}'), 150)" class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-3.5 rounded-2xl font-black text-sm transition active:scale-95">Withdraw & Decline</button>
                                                    <button @click="showDetails = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-900 py-3.5 rounded-2xl font-black text-sm transition mt-2">Close</button>
                                                @else
                                                    <button @click="showDetails = false" class="w-full bg-gray-900 hover:bg-black text-white py-3.5 rounded-2xl font-black text-sm transition">Close</button>
                                                @endif
                                            </div>
                                            
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- MODALS: Owner Actions --}}
                    @if($request->status_id == $pendingId)
                        {{-- Accept Modal --}}
                        @if($isMyTurn)
                        <div x-data="{ showApprove: false, showNote: false }" @open-approve-modal-{{ $request->id }}.window="showApprove = true" @keydown.escape.window="showApprove = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative z-50" x-cloak>
                            <div x-show="showApprove" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                            <div x-show="showApprove" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div @click.away="showApprove = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 p-8 sm:p-10">
                                        <h3 class="text-3xl font-black text-gray-900 mb-2">Accept Application</h3>
                                        <p class="text-sm text-gray-500 font-medium mb-6">You are finalizing the contract with {{ $request->renter->name }}.</p>
                                        <form action="{{ route('owner.reservations.approve', $request->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-8">
                                                <button x-show="!showNote" @click="showNote = true" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-50 text-teal-700 font-bold text-sm rounded-xl hover:bg-teal-100 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg> Add a welcome note (Optional)
                                                </button>
                                                <div x-show="showNote" x-transition style="display: none;">
                                                    <textarea x-ref="noteInput" name="response_note" rows="3" placeholder="Welcome aboard! Looking forward to meeting you..." class="w-full rounded-2xl border-gray-300 focus:border-teal-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                                </div>
                                            </div>
                                            <div class="flex gap-3">
                                                <button type="button" @click="showApprove = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3.5 font-bold transition">Back</button>
                                                <button type="submit" class="w-2/3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl py-3.5 font-black transition active:scale-95 shadow-sm">Accept & Finalize</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Decline Modal --}}
                        <div x-data="{ showDecline: false, showNote: false }" @open-decline-modal-{{ $request->id }}.window="showDecline = true" @keydown.escape.window="showDecline = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative z-50" x-cloak>
                            <div x-show="showDecline" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                            <div x-show="showDecline" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div @click.away="showDecline = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 p-8 sm:p-10">
                                        <h3 class="text-3xl font-black text-gray-900 mb-2">Decline Application</h3>
                                        <form action="{{ route('owner.reservations.reject', $request->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-8">
                                                <textarea name="reject_reason" required rows="3" placeholder="Reason for decline..." class="w-full rounded-2xl border-gray-300 focus:border-red-500 shadow-sm text-sm text-gray-700 resize-none"></textarea>
                                            </div>
                                            <div class="flex gap-3">
                                                <button type="button" @click="showDecline = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="w-1/2 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3.5 font-bold transition">Back to Details</button>
                                                <button type="submit" class="w-1/2 bg-red-600 hover:bg-red-700 text-white rounded-xl py-3.5 font-black transition active:scale-95 shadow-sm">Decline</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Propose New Dates Modal --}}
                        @if($isMyTurn)
                        <div x-data="{ 
                                showReschedule: false, showNote: false,
                                startDate: '{{ $startDateFormatted }}', 
                                endDate: '{{ $endDateFormatted }}', 
                                visitDate: '{{ $visitDateFormatted }}',
                                dailyRate: {{ $dailyRate }}, weeklyRate: {{ $weeklyRate }}, monthlyRate: {{ $monthlyRate }},
                                get durationText() {
                                    if(!this.startDate || !this.endDate) return '0 Days';
                                    let totalDays = Math.round((new Date(this.endDate) - new Date(this.startDate)) / 86400000);
                                    if (totalDays <= 0) return '0 Days';
                                    let m = Math.floor(totalDays / 30); let remDays = totalDays % 30; 
                                    let w = Math.floor(remDays / 7); let d = remDays % 7; 
                                    let text = []; 
                                    if (m > 0) text.push(m + ' Month(s)'); if (w > 0) text.push(w + ' Week(s)'); if (d > 0) text.push(d + ' Day(s)'); 
                                    return text.join(', ');
                                },
                                get total() {
                                    if(!this.startDate || !this.endDate) return 0;
                                    let rem = Math.round((new Date(this.endDate) - new Date(this.startDate)) / 86400000);
                                    if (rem <= 0) return 0; let price = 0;
                                    if (this.monthlyRate !== null) { price += Math.floor(rem / 30) * this.monthlyRate; rem %= 30; }
                                    if (this.weeklyRate !== null) { price += Math.floor(rem / 7) * this.weeklyRate; rem %= 7; }
                                    if (this.dailyRate !== null && rem > 0) { price += rem * this.dailyRate; rem = 0; } 
                                    if (rem > 0) { let s = this.dailyRate !== null ? this.dailyRate : (this.weeklyRate !== null ? this.weeklyRate / 7 : (this.monthlyRate !== null ? this.monthlyRate / 30 : 0)); price += rem * s; }
                                    return Math.round(price);
                                }
                            }" 
                            @open-reschedule-modal-{{ $request->id }}.window="showReschedule = true" @keydown.escape.window="showReschedule = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative z-50" x-cloak>
                            <div x-show="showReschedule" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                            <div x-show="showReschedule" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div @click.away="showReschedule = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative transform overflow-hidden rounded-[2.5rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-100 p-8 sm:p-10">
                                        <div class="mb-8 border-b border-gray-100 pb-4"><h3 class="text-3xl font-black text-gray-900">Propose Alternative Dates</h3></div>
                                        <form action="{{ route('owner.reservations.reschedule', $request->id) }}" method="POST">
                                            @csrf
                                            <div class="grid grid-cols-1 lg:grid-cols-5 gap-10">
                                                <div class="lg:col-span-3 space-y-6">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                                        <div><label class="block text-xs font-bold text-gray-700 uppercase mb-2">Start Date</label><input type="date" name="new_start_date" required x-model="startDate" class="w-full rounded-2xl border-gray-300 py-3"></div>
                                                        <div><label class="block text-xs font-bold text-gray-700 uppercase mb-2">End Date</label><input type="date" name="new_end_date" required x-model="endDate" :min="startDate" class="w-full rounded-2xl border-gray-300 py-3"></div>
                                                    </div>
                                                    <div><label class="block text-xs font-bold text-gray-700 uppercase mb-2">Visit Date</label><input type="date" name="new_visit_date" x-model="visitDate" :max="startDate" class="w-full sm:w-1/2 rounded-2xl border-gray-300 py-3"></div>
                                                    <textarea name="response_note" rows="3" placeholder="Explain changes..." class="w-full rounded-2xl border-gray-300 text-sm"></textarea>
                                                </div>
                                                <div class="lg:col-span-2">
                                                    <div class="bg-blue-50/50 p-8 rounded-[2rem] border border-blue-100 h-full flex flex-col justify-center">
                                                        <div class="mb-6 border-b border-blue-200/60 pb-4"><p class="text-[10px] uppercase font-black text-blue-400 mb-1">Duration</p><p class="text-xl font-black text-blue-900" x-text="durationText"></p></div>
                                                        <div><p class="text-[10px] uppercase font-black text-blue-400 mb-1">New Total Revenue</p><p class="text-3xl sm:text-4xl font-black text-orange-500 truncate" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total)"></p></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-10 flex justify-end gap-3 pt-6 border-t border-gray-100">
                                                <button type="button" @click="showReschedule = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="px-6 bg-gray-100 rounded-xl py-3.5 font-bold">Back</button>
                                                <button type="submit" :disabled="total === 0" class="px-8 bg-blue-600 text-white rounded-xl py-3.5 font-black shadow-sm disabled:opacity-50">Send Proposal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif

                    {{-- EARLY FINISH ONGOING MODALS (Owner side) --}}
                    @if($request->status_id == $ongoingId)
                        {{-- Owner Requesting Early Finish --}}
                        @if(!$pendingFinishMsg)
                        <div x-data="{ showReqFinish: false }" @open-request-finish-modal-{{ $request->id }}.window="showReqFinish = true" @keydown.escape.window="showReqFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative z-50" x-cloak>
                            <div x-show="showReqFinish" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                            <div x-show="showReqFinish" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div @click.away="showReqFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 p-8 sm:p-10">
                                        <h3 class="text-3xl font-black text-gray-900 mb-2">Request Early Finish</h3>
                                        <form action="{{ route('owner.reservations.finish.request', $request->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-8">
                                                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Reason for ending early</label>
                                                <textarea name="finish_reason" required rows="3" placeholder="Emergency structural edits required..." class="w-full rounded-2xl border-gray-300 focus:border-red-500 shadow-sm text-sm text-gray-700 resize-none"></textarea>
                                            </div>
                                            <div class="flex gap-3">
                                                <button type="button" @click="showReqFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="w-1/3 bg-gray-100 rounded-xl py-3.5 font-bold">Back</button>
                                                <button type="submit" class="w-2/3 bg-red-600 text-white rounded-xl py-3.5 font-black active:scale-95 shadow-sm">Submit Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Owner Approving Renter's Early Finish Request --}}
                        @if($isMyTurnFinish)
                        <div x-data="{ showAcceptFinish: false }" @open-accept-finish-modal-{{ $request->id }}.window="showAcceptFinish = true" @keydown.escape.window="showAcceptFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative z-50" x-cloak>
                            <div x-show="showAcceptFinish" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                            <div x-show="showAcceptFinish" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div @click.away="showAcceptFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-8 sm:p-10">
                                        <h3 class="text-3xl font-black text-gray-900 mb-2">Accept Termination</h3>
                                        <p class="text-sm text-gray-500 font-medium mb-6">This will immediately finalize the contract and mark it as completed. Are you sure?</p>
                                        <form action="{{ route('owner.reservations.finish.approve', $request->id) }}" method="POST">
                                            @csrf
                                            <div class="flex gap-3">
                                                <button type="button" @click="showAcceptFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="w-1/2 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-xl py-3.5 font-bold transition">Cancel</button>
                                                <button type="submit" class="w-1/2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl py-3.5 font-black transition active:scale-95 shadow-sm">Yes, Terminate</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Owner Rejecting Renter's Early Finish Request --}}
                        <div x-data="{ showRejectFinish: false }" @open-reject-finish-modal-{{ $request->id }}.window="showRejectFinish = true" @keydown.escape.window="showRejectFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative z-50" x-cloak>
                            <div x-show="showRejectFinish" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                            <div x-show="showRejectFinish" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                    <div @click.away="showRejectFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100 p-8 sm:p-10">
                                        <h3 class="text-3xl font-black text-gray-900 mb-2">Reject Request</h3>
                                        <form action="{{ route('owner.reservations.finish.reject', $request->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-8">
                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Reason for rejection</label>
                                                <textarea name="reject_reason" required rows="3" placeholder="Explain why you cannot accept early termination..." class="w-full rounded-2xl border-gray-300 focus:border-red-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                            </div>
                                            <div class="flex gap-3">
                                                <button type="button" @click="showRejectFinish = false; setTimeout(() => $dispatch('open-details-modal-{{ $request->id }}'), 300)" class="w-1/3 bg-gray-100 rounded-xl py-3.5 font-bold">Back</button>
                                                <button type="submit" class="w-2/3 bg-red-600 text-white rounded-xl py-3.5 font-black shadow-sm">Reject Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif
                @endforeach
            </div>
            <div class="mt-8">{{ $requests->links() }}</div>
        @else
            <div class="bg-white border border-gray-100 rounded-[2rem] p-12 text-center shadow-sm flex flex-col items-center">
                <span class="text-6xl mb-4">📥</span>
                <h3 class="text-xl font-black text-gray-900 mb-2">No active applications matches your current filter status</h3>
            </div>
        @endif
    </div>
</x-user-layout>