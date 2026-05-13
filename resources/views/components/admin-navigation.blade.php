<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="/images/logo.png" class="h-12 w-auto" alt="Lapak.in Logo">
                    <span class="text-2xl font-black tracking-tighter text-gray-900">
                        Lapak<span class="text-orange-500">.in</span>
                        <span class="text-[10px] bg-red-50 text-red-600 border border-red-100 font-extrabold px-2 py-0.5 rounded-md ml-1 tracking-normal align-middle shadow-sm">
                            ADMIN
                        </span>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="text-sm font-bold transition-all py-2 {{ request()->routeIs('admin.dashboard') ? 'text-teal-600 border-b-2 border-teal-600' : 'text-gray-700 hover:text-teal-600' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.user-verifications.index') }}" 
                       class="text-sm font-bold transition-all py-2 {{ request()->routeIs('admin.user-verifications.*') ? 'text-teal-600 border-b-2 border-teal-600' : 'text-gray-700 hover:text-teal-600' }}">
                        Verification Requests
                    </a>
                    <a href="{{ route('admin.listing-requests.index') }}" 
                       class="text-sm font-bold transition-all py-2 {{ request()->routeIs('admin.listing-requests.*') ? 'text-teal-600 border-b-2 border-teal-600' : 'text-gray-700 hover:text-teal-600' }}">
                        Listing Requests
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-bold text-gray-700 hover:text-teal-600 focus:outline-none transition gap-2.5 bg-gray-50 hover:bg-gray-100/80 px-4 py-2 rounded-xl border border-gray-100">
                            <div class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></div>
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="fill-current h-4 w-4 text-gray-400" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    
                    <x-slot name="content">
                        <div class="px-4 py-2 text-[11px] text-orange-600 bg-orange-50/50 border-b border-gray-50 font-extrabold uppercase tracking-wider">
                            Command Center
                        </div>
                        
                        <x-dropdown-link :href="route('profile.edit')">
                            Admin Profile
                        </x-dropdown-link>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 hover:bg-red-50">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>