<x-user-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-6">
            <a href="{{ route('spaces.show', $space->id) }}" class="text-sm font-bold text-gray-500 hover:text-teal-600 transition flex items-center gap-2">
                &larr; Back to {{ $space->name }}
            </a>
        </div>

        <div class="mb-8">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Request to Rent</h1>
            <p class="text-gray-500 font-medium mt-2">Send a proposal to the host. You won't be charged yet.</p>
        </div>

        @php
            // Fetch available rates to pass to Alpine for dynamic calculation
            $rates = \App\Models\SpaceRegistrationPrice::where('space_registration_id', $space->registration_id)
                ->join('pricing_types', 'space_registration_prices.pricing_type_id', '=', 'pricing_types.id')
                ->pluck('space_registration_prices.price', 'pricing_types.code')
                ->mapWithKeys(fn($item, $key) => [strtolower($key) => $item]);

            $dailyRate = $rates['daily'] ?? 'null';
            $weeklyRate = $rates['weekly'] ?? 'null';
            $monthlyRate = $rates['monthly'] ?? 'null';
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12" 
             x-data="rentForm({{ $dailyRate }}, {{ $weeklyRate }}, {{ $monthlyRate }})">
            
            <div class="lg:col-span-2">
                <form action="{{ route('rents.store', $space->id) }}" method="POST" class="space-y-8 bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    @csrf
                    
                    <div>
                        <div class="flex justify-between items-end mb-4">
                            <h3 class="text-xl font-black text-gray-900">1. Rental Duration</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-bold text-gray-700 mb-2">Start Date <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" id="start_date" x-model="startDate" required
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm font-medium text-gray-700">
                                @error('start_date') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="duration" class="block text-sm font-bold text-gray-700 mb-2">Total Duration (Days) <span class="text-red-500">*</span></label>
                                <input type="number" name="duration" id="duration" x-model="duration" required min="1" placeholder="e.g. 10"
                                       class="w-full rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm font-medium text-gray-700">
                                @error('duration') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2 mt-2">
                                <label class="block text-sm font-bold text-gray-400 mb-2">Calculated End Date</label>
                                <input type="text" :value="endDateFormatted" disabled class="w-full bg-gray-50 text-gray-500 rounded-xl border-gray-200 shadow-inner cursor-not-allowed font-bold">
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <h3 class="text-xl font-black text-gray-900 mb-1">2. Schedule a visit</h3>
                        <p class="text-sm text-gray-500 mb-4 font-medium">You must schedule a visit to see the space before your start date.</p>
                        <div>
                            <label for="visit_date" class="block text-sm font-bold text-gray-700 mb-2">Visit Date <span class="text-red-500">*</span></label>
                            <input type="date" name="visit_date" id="visit_date" x-model="visitDate" required
                                   min="{{ date('Y-m-d') }}"
                                   :max="startDate"
                                   class="w-full md:w-1/2 rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm font-medium text-gray-700">
                            @error('visit_date') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Dynamic Notes Section --}}
                    <div x-data="{ showNote: false }">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-xl font-black text-gray-900">3. Write your proposal <span class="text-gray-400 text-sm font-bold">(Optional)</span></h3>
                        </div>
                        <p class="text-sm text-gray-500 mb-4 font-medium">Introduce yourself, explain what you plan to sell/do, and state your intentions clearly.</p>
                        
                        <div x-show="!showNote" x-transition>
                            <button type="button" @click="showNote = true" class="text-teal-600 hover:text-teal-700 font-bold text-sm flex items-center gap-2 bg-teal-50 hover:bg-teal-100 px-4 py-2.5 rounded-xl transition shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Add a proposal message
                            </button>
                        </div>

                        <div x-show="showNote" x-transition x-cloak>
                            <textarea name="note" id="note" rows="5"
                                      placeholder="Hi {{ $space->owner->name }}, I am interested in renting this space for my coffee shop business..."
                                      class="w-full rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm font-medium text-gray-700 resize-none"></textarea>
                            @error('note') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                            <div class="flex justify-end mt-2">
                                <button type="button" @click="showNote = false; document.getElementById('note').value = ''" class="text-xs font-bold text-gray-400 hover:text-gray-600 transition">Cancel message</button>
                            </div>
                        </div>
                    </div>

                    {{-- NEW POLISHED PRICING SECTION --}}
                    <div class="mt-8">
                        
                        {{-- Available Rates Cards --}}
                        <div class="mb-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-3">Host's Configured Rates</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @if(isset($rates['daily']))
                                    <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-sm flex flex-col justify-center items-center text-center hover:border-teal-300 transition">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Daily</span>
                                        <span class="text-sm font-black text-teal-600">Rp {{ number_format($rates['daily'], 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if(isset($rates['weekly']))
                                    <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-sm flex flex-col justify-center items-center text-center hover:border-teal-300 transition">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Weekly</span>
                                        <span class="text-sm font-black text-teal-600">Rp {{ number_format($rates['weekly'], 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if(isset($rates['monthly']))
                                    <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-sm flex flex-col justify-center items-center text-center hover:border-teal-300 transition">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Monthly</span>
                                        <span class="text-sm font-black text-teal-600">Rp {{ number_format($rates['monthly'], 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Total Price Summary Receipt Widget --}}
                        <div class="p-6 bg-teal-50/50 rounded-2xl border border-teal-100 relative overflow-hidden">
                            <h4 class="text-base font-black text-teal-900 mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-teal-600">
                                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                </svg>
                                Payment Estimate
                            </h4>
                            
                            <div class="flex justify-between items-center mb-5">
                                <span class="text-sm font-bold text-gray-600">Optimized Breakdown</span>
                                <span class="text-xs font-black text-gray-900 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm" x-text="durationText"></span>
                            </div>
                            
                            <div class="flex justify-between items-end pt-5 border-t-2 border-dashed border-teal-200/60">
                                <div>
                                    <span class="text-xs font-black uppercase tracking-wider text-teal-800 block mb-1">Total Amount</span>
                                    <span class="text-[10px] text-teal-600/80 font-bold uppercase tracking-wide">You won't be charged yet</span>
                                </div>
                                <span class="text-3xl font-black text-teal-600">Rp <span x-text="formattedTotal"></span></span>
                            </div>
                        </div>

                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-4 rounded-2xl font-black text-lg transition-all active:scale-95 shadow-lg shadow-teal-600/30">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>

            {{-- Summary Right Column (Sidebar) --}}
            <div class="relative hidden lg:block">
                <div class="sticky top-8 bg-white p-6 rounded-[2rem] border border-gray-200 shadow-xl shadow-gray-100/50">
                    <div class="aspect-video w-full rounded-xl overflow-hidden mb-4 bg-gray-100">
                        <img src="{{ $space->cover_photo_url }}" alt="{{ $space->name }}" class="w-full h-full object-cover">
                    </div>
                    
                    <h3 class="text-lg font-black text-gray-900 mb-2">{{ $space->name }}</h3>
                    
                    <div class="space-y-2 text-sm font-medium text-gray-600 pb-4">
                        <p class="flex items-start gap-2">
                            <span class="text-teal-600 mt-0.5">📍</span> 
                            <span>{{ $space->location->address }}, {{ $space->location->city }}</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="text-teal-600">📏</span> 
                            <span>{{ $space->formatted_size }}</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="text-teal-600">👤</span> 
                            <span>Hosted by {{ $space->owner->name }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('rentForm', (dailyRate, weeklyRate, monthlyRate) => ({
                startDate: '',
                duration: 1,
                visitDate: '',
                dailyRate: dailyRate,
                weeklyRate: weeklyRate,
                monthlyRate: monthlyRate,

                get endDateFormatted() {
                    if (!this.startDate || !this.duration) return 'Please select a start date';
                    let date = new Date(this.startDate);
                    date.setDate(date.getDate() + parseInt(this.duration));
                    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                },

                get durationText() {
                    let totalDays = parseInt(this.duration || 0);
                    if (totalDays <= 0) return '0 Days';
                    
                    let m = Math.floor(totalDays / 30); 
                    let remDays = totalDays % 30; 
                    let w = Math.floor(remDays / 7); 
                    let d = remDays % 7; 
                    
                    let text = []; 
                    if (m > 0) text.push(m + ' Month(s)'); 
                    if (w > 0) text.push(w + ' Week(s)'); 
                    if (d > 0) text.push(d + ' Day(s)'); 
                    
                    return text.length > 0 ? text.join(', ') : '0 Days';
                },

                get total() {
                    let rem = parseInt(this.duration || 0);
                    if (rem <= 0) return 0;
                    
                    let price = 0;

                    if (this.monthlyRate !== null) {
                        let m = Math.floor(rem / 30);
                        price += m * this.monthlyRate;
                        rem %= 30;
                    }
                    if (this.weeklyRate !== null) {
                        let w = Math.floor(rem / 7);
                        price += w * this.weeklyRate;
                        rem %= 7;
                    }
                    if (this.dailyRate !== null && rem > 0) {
                        price += rem * this.dailyRate;
                        rem = 0;
                    } 
                    if (rem > 0) {
                        let smallestRate = this.dailyRate !== null ? this.dailyRate : (this.weeklyRate !== null ? this.weeklyRate / 7 : (this.monthlyRate !== null ? this.monthlyRate / 30 : 0));
                        price += rem * smallestRate;
                    }
                    return Math.round(price);
                },

                get formattedTotal() {
                    return new Intl.NumberFormat('id-ID').format(this.total);
                },

                init() {
                    this.$watch('startDate', value => {
                        if (this.visitDate && value < this.visitDate) this.visitDate = '';
                    });
                }
            }));
        });
    </script>
</x-user-layout>