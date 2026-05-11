<x-guest-layout>
    <x-auth.auth-card>
        {{-- Heading --}}
        <div class="text-center mb-14">

            <h2 class="text-5xl font-bold text-black mb-3">
                Selamat Datang Kembali
            </h2>
            <p class="text-2xl text-gray-500">
                Masuk ke akun Lapak.in Anda
            </p>

        </div>

        {{-- Session Status --}}
        <x-auth-session-status class="mb-6":status="session('status')"/>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                {{-- Email / Username --}}
                <div class="mb-6">
                    <x-input-label for="auth_id" :value="__('Email Or Username')" class="text-xl font-semibold text-gray-700 mb-2"/>
                    <x-text-input id="auth_id" class="block w-full rounded-2xl border border-gray-200 bg-gray-100 px-5 py-4 text-lg 
                    placeholder-gray-400 shadow-sm focus:border-orange-400 focus:ring-orange-400" type="text" name="auth_id" :value="old('auth_id')" 
                    required autofocus autocomplete="auth_id" placeholder=""/>
                     <x-input-error :messages="$errors->get('auth_id')" class="mt-2" />
                </div>

                {{-- Password --}}
              {{-- Password --}}
                <div class="mb-5">
                    <x-input-label for="password" :value="__('Password')" class="text-xl font-semibold text-gray-700 mb-2"/>
                    <div class="relative">
                        <x-text-input id="password" class="block w-full rounded-2xl border border-gray-200 bg-gray-100 px-5 py-4 pr-14 text-lg 
                        placeholder-gray-400 shadow-sm focus:border-orange-400 focus:ring-orange-400" type="password" name="password" 
                        required autocomplete="current-password" placeholder="••••••••" />
                        <x-auth.password-toggle target="password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Remember Me --}}
                <div class="mb-8 flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-orange-500 focus:ring-orange-400" name="remember">
                    <label for="remember_me" class="ml-3 text-base text-gray-600">
                        Ingat saya
                    </label>
                </div>

                {{-- Login Button --}}
                <button type="submit" class="w-full rounded-2xl bg-orange-500 py-4 text-xl font-semibold text-white shadow-lg 
                transition duration-200 hover:bg-orange-600">
                    Masuk
                </button>

                {{-- Bottom Links --}}
                <div class="mt-6 text-center">
                    <a href="{{ route('register') }}" class="text-lg text-cyan-600 hover:underline">
                        Belum punya akun? Daftar sekarang
                    </a>
                </div>

            </form>
    </x-auth.auth-card>

</x-guest-layout>



