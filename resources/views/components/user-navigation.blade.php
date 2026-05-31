<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">

            {{-- Logo --}}
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <img src="/images/logo.png" class="h-12 w-auto" alt="Logo">

                    <span class="text-2xl font-black tracking-tight text-gray-900">
                        Lapak<span class="text-orange-500">.in</span>
                    </span>
                </a>
            </div>

            {{-- Navigation --}}
            <div class="hidden md:flex items-center gap-8">

                <a href="#"
                   class="text-sm font-semibold text-gray-700 hover:text-orange-500 transition duration-200">
                    Cari Lahan
                </a>

                <a href="#"
                   class="text-sm font-semibold text-gray-700 hover:text-orange-500 transition duration-200">
                    Lahan Sewa
                </a>

                {{-- Action Button --}}
                @auth
                    @if($btn = auth()->user()->action_btn)
                        <a href="{{ $btn->url }}"
                           class="{{ $btn->color }} text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md hover:opacity-90 transition-all duration-200 active:scale-95">
                            {{ $btn->label }}
                        </a>

                    @elseif(auth()->user()->ver_status == \App\Models\Status::USR_VERIFY_PENDING)
                        <span class="text-gray-400 text-sm font-medium italic">
                            Verification Pending...
                        </span>
                    @endif
                @endauth

                {{-- Auth Section --}}
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-gray-100 transition focus:outline-none">

                                <div class="w-9 h-9 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold text-sm uppercase">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>

                                <div class="text-sm font-semibold text-gray-700">
                                    {{ Auth::user()->name }}
                                </div>

                                <svg class="fill-current h-4 w-4 text-gray-500"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                Profile
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>

                @else
                    <div class="flex items-center gap-3">

                        <a href="{{ route('login') }}"
                           class="text-sm font-semibold text-gray-700 hover:text-orange-500 transition">
                            Login
                        </a>

                        <a href="{{ route('register') }}"
                           class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all duration-200 active:scale-95">
                            Register
                        </a>

                    </div>
                @endauth

            </div>

        </div>
    </div>
</nav>