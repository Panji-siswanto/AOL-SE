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

                        {{-- My Rents Icon --}}
                        <a href="{{ route('rents.index') }}" class="text-blue-400 hover:text-blue-500 hover:bg-blue-50 p-2.5 rounded-full transition-all duration-200" title="My Rent Requests">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </a>

                        {{-- Owner Only Features --}}
                        @if(auth()->user()->hasRole('owner'))
                            <div class="h-6 w-px bg-gray-200 mx-2"></div> {{-- Divider --}}

                            {{-- Manage Spaces Icon --}}
                            <a href="{{ route('owner.spaces.index') }}" class="text-orange-400 hover:text-orange-500 hover:bg-orange-50 p-2.5 rounded-full transition-all duration-200" title="Manage My Spaces">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                </svg>
                            </a>

                            {{-- Incoming Reservations Icon --}}
                            <a href="{{ route('owner.reservations.index') }}" class="text-purple-400 hover:text-purple-500 hover:bg-purple-50 p-2.5 rounded-full transition-all duration-200" title="Incoming Reservations">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 12.839a2.25 2.25 0 0 0-.1.661Z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                @else
                    <button @click.prevent="window.dispatchEvent(new CustomEvent('open-login-modal'))" class="text-gray-400 hover:text-yellow-500 hover:bg-yellow-50 p-2.5 rounded-full transition-all duration-200" title="Saved Spaces">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 22.096c0 .514-.61.776-1.01.442L12 18.75l-4.49 3.788c-.4.334-1.01.072-1.01-.442V4.5A1.5 1.5 0 0 1 8 3h8a1.5 1.5 0 0 1 1.5 1.5v17.596Z" />
                        </svg>
                    </button>
                @endauth

                {{-- Action Button (Hidden for Owners to prevent redundancy) --}}
                @auth
                    @if(!auth()->user()->hasRole('owner'))
                        @php $btn = auth()->user()->action_btn; @endphp
                        @if($btn)
                            <a href="{{ $btn->url }}"
                               class="{{ $btn->color }} text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-sm hover:shadow hover:-translate-y-0.5 transition-all duration-200 active:scale-95">
                                {{ $btn->label }}
                            </a>
                        @endif
                    @endif
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