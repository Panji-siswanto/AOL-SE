{{-- resources/views/auth/register.blade.php --}}

<x-guest-layout>

    <x-auth.auth-card>

        {{-- Heading --}}
        <div class="text-center mb-14">
            <h2 class="text-5xl font-bold text-black mb-3">
                Buat Akun Baru
            </h2>
            <p class="text-2xl text-gray-500">
                Daftar dan mulai menggunakan Lapak.in
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            {{-- Name --}}
            <div class="mb-6">
                <x-input-label for="name" :value="__('Nama Lengkap')" class="text-xl font-semibold text-gray-700 mb-2"/>
                <x-text-input id="name" class="block w-full rounded-2xl border border-gray-200 bg-gray-100 px-5 py-4 
                text-lg placeholder-gray-400 shadow-sm focus:border-orange-400 focus:ring-orange-400" type="text" name="name" 
                :value="old('name')" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap"/>
                <x-input-error :messages="$errors->get('name')" class="mt-2"/>
            </div>

            {{-- Username --}}
            <div class="mb-6">
                <x-input-label for="username" :value="__('Username')" class="text-xl font-semibold text-gray-700 mb-2"/>
                <x-text-input id="username" class="block w-full rounded-2xl border border-gray-200 bg-gray-100 px-5 py-4 text-lg
                 placeholder-gray-400 shadow-sm focus:border-orange-400 focus:ring-orange-400" type="text" name="username" 
                  :value="old('username')" required autocomplete="username" placeholder="Masukkan username"/>
                <x-input-error :messages="$errors->get('username')" class="mt-2"/>
            </div>

            {{-- Phone --}}
            <div class="mb-6">
                <x-input-label for="phone" :value="__('Nomor Telepon')" class="text-xl font-semibold text-gray-700 mb-2"/>
                <x-text-input id="phone" class="block w-full rounded-2xl border border-gray-200 bg-gray-100 px-5 py-4 
                text-lg placeholder-gray-400 shadow-sm focus:border-orange-400 focus:ring-orange-400" type="text" name="phone" 
                :value="old('phone')" autocomplete="tel" placeholder="08xxxxxxxxxx"/>
                <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
            </div>

            {{-- Email --}}
            <div class="mb-6">
                <x-input-label for="email" :value="__('Email')" class="text-xl font-semibold text-gray-700 mb-2"/>
                <x-text-input id="email" class="block w-full rounded-2xl border border-gray-200 bg-gray-100 px-5 py-4 text-lg 
                placeholder-gray-400 shadow-sm focus:border-orange-400 focus:ring-orange-400" type="email" name="email" 
                :value="old('email')" required autocomplete="email" placeholder="Masukan email"/>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password --}}
            <div class="mb-6">
                <x-input-label for="password" :value="__('Password')" class="text-xl font-semibold text-gray-700 mb-2"/>
                <div class="relative">
                    <x-text-input id="password" class="block w-full rounded-2xl border border-gray-200 bg-gray-100 px-5 py-4 pr-14 text-lg 
                    placeholder-gray-400 shadow-sm focus:border-orange-400 focus:ring-orange-400" type="password" name="password" 
                    required autocomplete="new-password" placeholder="••••••••"/>
                    <x-auth.password-toggle target="password" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2"/>
            </div>

            {{-- Confirm Password --}}
            <div class="mb-8">

                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-xl font-semibold text-gray-700 mb-2"/>
                <div class="relative">
                    <x-text-input id="password_confirmation" class="block w-full rounded-2xl border border-gray-200 bg-gray-100 px-5 py-4 pr-14 text-lg 
                    placeholder-gray-400 shadow-sm focus:border-orange-400 focus:ring-orange-400" type="password" name="password_confirmation"
                    required autocomplete="new-password" placeholder="••••••••"/>
                    <x-auth.password-toggle target="password_confirmation" />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"/>

            </div>

            {{-- Register Button --}}
            <button type="submit" class="w-full rounded-2xl bg-orange-500 py-4 text-xl font-semibold text-white shadow-lg transition duration-200 hover:bg-orange-600">
                Daftar
            </button>
            {{-- Bottom Links --}}
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-lg text-cyan-600 hover:underline">
                    Sudah punya akun? Masuk sekarang
                </a>
            </div>

        </form>

    </x-auth.auth-card>

</x-guest-layout>