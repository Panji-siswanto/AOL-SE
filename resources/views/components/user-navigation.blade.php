<nav class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">

            {{-- Logo --}}
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <img src="/images/logo.png" class="h-10 w-auto group-hover:scale-105 transition-transform duration-300" alt="Logo">
                    <span class="text-2xl font-black tracking-tight text-gray-900">
                        Lapak<span class="text-orange-500">.in</span>
                    </span>
                </a>
            </div>

            {{-- Navigation Right Side --}}
            <div class="hidden md:flex items-center gap-5">

                {{-- Feature Icons --}}
                @auth
                    <div class="flex items-center gap-2">
                        {{-- Bookmarks Icon --}}
                        <a href="{{ route('bookmarks.index') }}" class="text-teal-400 hover:text-teal-500 hover:bg-teal-50 p-2.5 rounded-full transition-all duration-200" title="Saved Spaces">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 22.096c0 .514-.61.776-1.01.442L12 18.75l-4.49 3.788c-.4.334-1.01.072-1.01-.442V4.5A1.5 1.5 0 0 1 8 3h8a1.5 1.5 0 0 1 1.5 1.5v17.596Z" />
                            </svg>
                        </a>

                        {{-- NEW: My Rents Icon --}}
                        <a href="{{ route('rents.index') }}" class="text-blue-400 hover:text-blue-500 hover:bg-blue-50 p-2.5 rounded-full transition-all duration-200" title="My Rent Requests">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </a>
                    </div>
                @else
                    <button @click.prevent="window.dispatchEvent(new CustomEvent('open-login-modal'))" class="text-gray-400 hover:text-yellow-500 hover:bg-yellow-50 p-2.5 rounded-full transition-all duration-200" title="Saved Spaces">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 22.096c0 .514-.61.776-1.01.442L12 18.75l-4.49 3.788c-.4.334-1.01.072-1.01-.442V4.5A1.5 1.5 0 0 1 8 3h8a1.5 1.5 0 0 1 1.5 1.5v17.596Z" />
                        </svg>
                    </button>
                @endauth

                {{-- Action Button --}}
                @auth
                    @php $btn = auth()->user()->action_btn; @endphp
                    <a href="{{ $btn->url }}"
                       class="{{ $btn->color }} text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-sm hover:shadow hover:-translate-y-0.5 transition-all duration-200 active:scale-95">
                        {{ $btn->label }}
                    </a>
                @endauth

                {{-- Auth Section --}}
                @auth
                    <div class="h-8 w-px bg-gray-200 mx-1"></div> {{-- Subtle Vertical Divider --}}
                    
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-3 p-1 pr-3 rounded-full border border-transparent hover:border-gray-200 hover:bg-gray-50 transition-all focus:outline-none">

                                <div class="w-9 h-9 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold text-sm uppercase shadow-sm">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>

                                <div class="text-sm font-bold text-gray-700">
                                    {{ Auth::user()->name }}
                                </div>

                                <svg class="fill-current h-4 w-4 text-gray-400" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')" class="font-medium text-gray-700">
                                Profile
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();" 
                                    class="font-medium text-red-600 hover:text-red-700 hover:bg-red-50">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>

                @else
                    <div class="flex items-center gap-4 ml-2">
                        <a href="{{ route('login') }}"
                           class="text-sm font-bold text-gray-600 hover:text-gray-900 transition px-2">
                            Log in
                        </a>

                        <a href="{{ route('register') }}"
                           class="bg-gray-900 hover:bg-black text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-md transition-all duration-200 active:scale-95">
                            Sign up
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>