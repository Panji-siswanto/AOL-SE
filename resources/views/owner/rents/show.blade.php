<x-user-layout>
    <style> [x-cloak] { display: none !important; } </style>
    
    {{-- ADDED x-data HERE! This tells Alpine to listen to the buttons inside this container --}}
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data>
        
        {{-- Header & Navigation --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <a href="{{ route('owner.reservations.index') }}" class="text-sm font-bold text-gray-500 hover:text-teal-600 transition flex items-center gap-2">
                &larr; Back to Reservations
            </a>
            
            {{-- Status Badge --}}
            <div>
                @if($rentRequest->status_id == \App\Models\Status::RNT_REQ_PENDING)
                    <span class="bg-orange-50 text-orange-600 px-4 py-2 rounded-xl text-sm font-black border border-orange-100 tracking-wide uppercase shadow-sm">Action Required</span>
                @elseif($rentRequest->status_id == \App\Models\Status::RNT_REQ_ACCEPTED)
                    <span class="bg-teal-50 text-teal-600 px-4 py-2 rounded-xl text-sm font-black border border-teal-100 tracking-wide uppercase shadow-sm">Accepted</span>
                @elseif($rentRequest->status_id == \App\Models\Status::RNT_REQ_REJECTED)
                    <span class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-sm font-black border border-red-100 tracking-wide uppercase shadow-sm">Rejected</span>
                @else
                    <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-sm font-black border border-gray-200 tracking-wide uppercase shadow-sm">{{ $rentRequest->status->name }}</span>
                @endif
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 font-bold text-sm shadow-sm">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="mb-6 bg-teal-50 text-teal-700 p-4 rounded-xl border border-teal-100 font-bold text-sm shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-12 items-start">
            
            {{-- Main Content (Left Column) --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Space Summary --}}
                <div class="bg-white p-6 sm:p-8 rounded-[2rem] border border-gray-100 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 items-start">
                    <div class="md:col-span-1 w-full aspect-video rounded-xl overflow-hidden bg-gray-100 relative">
                        <img src="{{ $rentRequest->space->cover_photo_url }}" alt="Space Cover" class="absolute inset-0 w-full h-full object-cover">
                    </div>
                    <div class="md:col-span-2 w-full min-w-0"> 
                        <h2 class="text-2xl font-black text-gray-900 mb-3">{{ $rentRequest->space->name }}</h2>
                        <div class="space-y-2 text-sm font-medium text-gray-600">
                            <p class="flex items-start gap-2">
                                <span class="text-teal-600 mt-0.5">📍</span> 
                                <span class="truncate">{{ $rentRequest->space->location->address }}, {{ $rentRequest->space->location->city }}</span>
                            </p>
                            <p class="flex items-center gap-2">
                                <span class="text-teal-600">📏</span> {{ $rentRequest->space->formatted_size }}
                            </p>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Applicant</p>
                                <p class="font-black text-gray-900 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs">{{ substr($rentRequest->renter->name, 0, 1) }}</span>
                                    {{ $rentRequest->renter->name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- The Renter's Proposal Message --}}
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span>💬</span> Renter's Proposal
                    </h3>
                    
                    @php
                        // Fetching using the explicit type_id for Application Pitches
                        $proposalMsg = $rentRequest->messages->where('type_id', \App\Models\Status::MSG_APPLICATION)->first();
                    @endphp

                    @if($proposalMsg && $proposalMsg->message)
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                            <p class="text-gray-700 font-medium leading-relaxed whitespace-pre-line">{{ $proposalMsg->message }}</p>
                        </div>
                    @else
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 border-dashed text-center">
                            <p class="text-sm font-bold text-gray-400 mb-1">No proposal message attached.</p>
                            <p class="text-xs font-medium text-gray-400">The applicant opted not to leave an initial note.</p>
                        </div>
                    @endif
                </div>

                {{-- Timeline Details --}}
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span>📅</span> Timeline Details
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        
                        @if($rentRequest->visit_date)
                            <div class="p-5 bg-teal-50 rounded-2xl border border-teal-200">
                                <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 block mb-1">Proposed Visit</span>
                                <span class="text-lg font-black text-teal-900">{{ \Carbon\Carbon::parse($rentRequest->visit_date)->format('M d, Y') }}</span>
                            </div>
                        @else
                            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 opacity-50">
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">Proposed Visit</span>
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

            {{-- Action Widget (Right Column) --}}
            <div class="lg:col-span-1 sticky top-28 bg-white p-6 sm:p-8 rounded-[2rem] border border-gray-200 shadow-xl shadow-gray-100/50">
                
                <h3 class="text-sm font-black uppercase tracking-wider text-gray-400 mb-6 border-b border-gray-100 pb-4">Financial Summary</h3>
                
               <div class="space-y-4 mb-8">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-500">Agreed Rate ({{ $rentRequest->pricing->pricingType->name ?? 'Base' }})</span>
                        <span class="text-sm font-black text-gray-900">Rp {{ number_format($rentRequest->pricing->price ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-500">Duration</span>
                        <span class="text-sm font-black text-gray-900">
                            @if($rentRequest->pricing && $rentRequest->pricing->price > 0)
                                @php
                                    $units = floor($rentRequest->total_price / $rentRequest->pricing->price);
                                    $code = strtolower($rentRequest->pricing->pricingType->code ?? '');
                                    $baseWord = match($code) {
                                        'daily' => 'Day',
                                        'weekly' => 'Week',
                                        'monthly' => 'Month',
                                        default => 'Unit'
                                    };
                                @endphp
                                
                                {{ $units }} {{ \Illuminate\Support\Str::plural($baseWord, $units) }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <span class="text-lg font-black text-gray-900">Total Revenue</span>
                        <span class="text-2xl font-black text-teal-600">Rp {{ number_format($rentRequest->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if($rentRequest->status_id == \App\Models\Status::RNT_REQ_PENDING)
                    <div class="space-y-3 pt-6 border-t border-gray-100">
                        {{-- UPDATED: Using Alpine's native $dispatch() for much cleaner code --}}
                        <button @click.prevent="$dispatch('open-approve-modal')" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3.5 rounded-2xl font-black transition-all active:scale-95 shadow-lg shadow-teal-600/30">
                            Accept Application
                        </button>
                        
                        <button @click.prevent="$dispatch('open-reschedule-modal')" class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 py-3.5 rounded-2xl font-black transition-all active:scale-95 shadow-sm">
                            Propose New Visit Date
                        </button>
                        
                        <button @click.prevent="$dispatch('open-decline-modal')" class="w-full bg-white hover:bg-red-50 text-red-500 hover:text-red-600 border-2 border-red-100 hover:border-red-200 py-3.5 rounded-2xl font-black transition-all active:scale-95">
                            Decline Application
                        </button>
                    </div>
                @elseif($rentRequest->status_id == \App\Models\Status::RNT_REQ_ACCEPTED)
                    <div class="pt-4 border-t border-gray-100 text-center">
                        <div class="w-16 h-16 bg-teal-50 text-teal-500 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </div>
                        <h4 class="font-black text-gray-900">Application Approved</h4>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODALS SECTION --}}
    @if($rentRequest->status_id == \App\Models\Status::RNT_REQ_PENDING)
        
        {{-- 1. Approve Modal --}}
        <div x-data="{ showApprove: false }" @open-approve-modal.window="showApprove = true" @keydown.escape.window="showApprove = false" class="relative z-50" x-cloak>
            <div x-show="showApprove" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
            <div x-show="showApprove" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div @click.away="showApprove = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                        <div class="bg-white px-8 pb-8 pt-10 sm:p-10">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-teal-100 sm:mx-0 sm:h-12 sm:w-12">
                                    <svg class="h-6 w-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-xl font-black text-gray-900">Accept Application</h3>
                                    <p class="mt-2 text-sm text-gray-500 font-medium">You are about to accept the application from {{ $rentRequest->renter->name }}.</p>
                                </div>
                            </div>
                            <form action="{{ route('owner.reservations.approve', $rentRequest->id) }}" method="POST" class="mt-6">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Message to Renter (Optional)</label>
                                    <textarea name="response_note" rows="3" placeholder="Hi, looking forward to meeting you on the visit date!" class="w-full rounded-2xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                </div>
                                <div class="mt-8 sm:flex sm:flex-row-reverse gap-3">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-teal-600 px-6 py-3 text-sm font-black text-white shadow-sm hover:bg-teal-700 sm:w-auto transition active:scale-95">Accept & Create Contract</button>
                                    <button type="button" @click="showApprove = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Reschedule Modal --}}
        <div x-data="{ showReschedule: false }" @open-reschedule-modal.window="showReschedule = true" @keydown.escape.window="showReschedule = false" class="relative z-50" x-cloak>
            <div x-show="showReschedule" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
            <div x-show="showReschedule" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div @click.away="showReschedule = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                        <div class="bg-white px-8 pb-8 pt-10 sm:p-10">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-12 sm:w-12">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-xl font-black text-gray-900">Propose Alternative Date</h3>
                                    <p class="mt-2 text-sm text-gray-500 font-medium">Suggest a different site visit date to {{ $rentRequest->renter->name }}.</p>
                                </div>
                            </div>
                            <form action="{{ route('owner.reservations.reschedule', $rentRequest->id) }}" method="POST" class="mt-6">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">New Proposed Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="new_visit_date" required min="{{ date('Y-m-d') }}" max="{{ \Carbon\Carbon::parse($rentRequest->start_date)->subDay()->format('Y-m-d') }}" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-medium text-sm text-gray-700">
                                    <p class="text-[10px] text-gray-400 mt-1 font-bold">Must be before the contract start date ({{ \Carbon\Carbon::parse($rentRequest->start_date)->format('M d, Y') }}).</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Reason / Message <span class="text-red-500">*</span></label>
                                    <textarea name="response_note" required rows="3" placeholder="Hi, I am unavailable on your requested date. Could we do this date instead?" class="w-full rounded-2xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                </div>
                                <div class="mt-8 sm:flex sm:flex-row-reverse gap-3">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-sm hover:bg-blue-700 sm:w-auto transition active:scale-95">Send Proposal</button>
                                    <button type="button" @click="showReschedule = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Decline Modal --}}
        <div x-data="{ showDecline: false }" @open-decline-modal.window="showDecline = true" @keydown.escape.window="showDecline = false" class="relative z-50" x-cloak>
            <div x-show="showDecline" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
            <div x-show="showDecline" x-transition class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div @click.away="showDecline = false" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                        <div class="bg-white px-8 pb-8 pt-10 sm:p-10">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-12 sm:w-12">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-xl font-black text-gray-900">Decline Application</h3>
                                    <p class="mt-2 text-sm text-gray-500 font-medium">Please provide a reason for declining. This helps renters understand why their application was not accepted.</p>
                                </div>
                            </div>
                            
                            {{-- The actual form that submits to the backend --}}
                            <form action="{{ route('owner.reservations.reject', $rentRequest->id) }}" method="POST" class="mt-6">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Reason for Declining <span class="text-red-500">*</span></label>
                                    <textarea name="reject_reason" required rows="3" placeholder="I'm sorry, but the space is currently undergoing maintenance..." class="w-full rounded-2xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm font-medium text-sm text-gray-700 resize-none"></textarea>
                                </div>
                                <div class="mt-8 sm:flex sm:flex-row-reverse gap-3">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-6 py-3 text-sm font-black text-white shadow-sm hover:bg-red-700 sm:w-auto transition active:scale-95">Decline Application</button>
                                    <button type="button" @click="showDecline = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">Cancel</button>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</x-user-layout>