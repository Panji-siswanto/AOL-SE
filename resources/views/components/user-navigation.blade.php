<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <img src="/images/logo.png" class="h-12 w-auto" alt="Logo">
                    <span class="text-2xl font-black tracking-tighter text-gray-900">Lapak<span class="text-orange-500">.in</span></span>
                </a>
            </div>

            <div class="flex items-center gap-6">
                <a href="#" class="text-sm font-bold text-gray-700 hover:text-teal-600 transition">Cari Lahan</a>
                <a href="#" class="text-sm font-bold text-gray-700 hover:text-teal-600 transition">Lahan Sewa</a>
                
                <div class="flex items-center gap-6">
            @auth
                @if($btn = auth()->user()->action_btn)
                    <a href="{{ $btn->url }}" 
                    class="{{ $btn->color }} text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg transition-all active:scale-95">
                        {{ $btn->label }}
                    </a>
                @elseif(auth()->user()->ver_status == \App\Models\Status::USR_VERIFY_PENDING)
                    {{-- Optional: Show nothing or a simple gray 'Pending' text --}}
                    <span class="text-gray-400 text-sm font-medium italic">Verification Pending...</span>
                @endif
            @endauth

    </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-bold text-gray-500 hover:text-gray-700 focus:outline-none transition">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>