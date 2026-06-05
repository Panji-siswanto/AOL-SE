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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12" 
             x-data="rentForm('{{ $selectedPricing->pricingType->code }}', {{ (int) $selectedPricing->price }})">
            
            <div class="lg:col-span-2">
                <form action="{{ route('rents.store', $space->id) }}" method="POST" class="space-y-8 bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    @csrf
                    
                    <input type="hidden" name="pricing_id" value="{{ $selectedPricing->id }}">

                    <div>
                        <div class="flex justify-between items-end mb-4">
                            <h3 class="text-xl font-black text-gray-900">1. Rental Duration</h3>
                            <span class="bg-teal-50 text-teal-700 px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider">
                                {{ $selectedPricing->pricingType->name }} Rate
                            </span>
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
                                <label for="duration" class="block text-sm font-bold text-gray-700 mb-2">Duration (<span x-text="typeLabel"></span>s) <span class="text-red-500">*</span></label>
                                <input type="number" name="duration" id="duration" x-model="duration" required min="1"
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

                    <div>
                        <h3 class="text-xl font-black text-gray-900 mb-1">3. Write your proposal</h3>
                        <p class="text-sm text-gray-500 mb-4 font-medium">Introduce yourself, explain what you plan to sell/do, and state your intentions clearly.</p>
                        <div>
                            <textarea name="note" id="note" rows="5" required
                                      placeholder="Hi {{ $space->owner->name }}, I am interested in renting this space for my coffee shop business..."
                                      class="w-full rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm font-medium text-gray-700 resize-none"></textarea>
                            @error('note') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Total Price Summary Widget --}}
                    <div class="mt-6 p-6 bg-gray-50 rounded-2xl border border-gray-200">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm font-bold text-gray-500">Base Rate</span>
                            <span class="text-sm font-black text-gray-900">Rp {{ number_format($selectedPricing->price, 0, ',', '.') }} / <span x-text="typeLabel"></span></span>
                        </div>
                        <div class="flex justify-between items-center mb-5">
                            <span class="text-sm font-bold text-gray-500">Selected Duration</span>
                            <span class="text-sm font-black text-gray-900"><span x-text="duration"></span> <span x-text="typeLabel"></span>(s)</span>
                        </div>
                        <div class="flex justify-between items-center pt-5 border-t border-gray-200">
                            <span class="text-lg font-black text-gray-900">Total Price</span>
                            <span class="text-2xl font-black text-orange-500">Rp <span x-text="formattedTotal"></span></span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-4 rounded-2xl font-black transition-all active:scale-95 shadow-lg shadow-orange-500/30">
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>

            {{-- Summary Right Column --}}
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
            Alpine.data('rentForm', (type, price) => ({
                startDate: '',
                duration: 1,
                visitDate: '',
                type: type,
                price: price,
                
                get typeLabel() {
                    if (this.type === 'daily') return 'Day';
                    if (this.type === 'weekly') return 'Week';
                    if (this.type === 'monthly') return 'Month';
                    return '';
                },

                get endDateFormatted() {
                    if (!this.startDate || !this.duration) return 'Please select a start date';
                    let date = new Date(this.startDate);
                    let dur = parseInt(this.duration);

                    if (this.type === 'daily') {
                        date.setDate(date.getDate() + dur);
                    } else if (this.type === 'weekly') {
                        date.setDate(date.getDate() + (dur * 7));
                    } else if (this.type === 'monthly') {
                        date.setMonth(date.getMonth() + dur);
                    }
                    
                    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
                },

                get formattedTotal() {
                    let total = parseInt(this.duration || 0) * this.price;
                    return new Intl.NumberFormat('id-ID').format(total);
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