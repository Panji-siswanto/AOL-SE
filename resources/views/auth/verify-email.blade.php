<x-guest-layout>
    <div class="max-w-md mx-auto my-12 p-8 bg-white rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-100/50 text-center">
        
        <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl shadow-inner border border-orange-100/50 animate-bounce">
            💌
        </div>

        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-2">
            {{ __('Verifikasi Email Anda') }}
        </h2>
        
        <p class="text-sm text-gray-500 leading-relaxed mb-6 px-2">
            {{ __('Terima kasih telah mendaftar di Lapak.in! Sebelum memulai eksplorasi dan menyewa lahan, mohon verifikasi alamat email Anda melalui tautan yang baru saja kami kirimkan.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 p-4 bg-teal-50 border border-teal-100 rounded-2xl text-teal-800 text-xs font-bold flex items-center justify-center gap-2 animate-fade-in">
                <span>✨</span>
                <span>{{ __('Tautan verifikasi baru telah berhasil dikirim ke email Anda.') }}</span>
            </div>
        @endif

        <div class="space-y-4 pt-2 border-t border-gray-50">
            
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-bold text-sm py-3.5 px-6 rounded-xl shadow-lg shadow-orange-500/25 transition-all duration-150 outline-none focus:ring-2 focus:ring-orange-400">
                    {{ __('Kirim Ulang Email Verifikasi') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-gray-600 underline transition-colors outline-none py-1">
                    {{ __('Ganti Akun / Keluar') }}
                </button>
            </form>

        </div>

        <p class="mt-8 text-[11px] text-gray-400">
            {{ __('Tidak menerima email? Periksa folder Spam atau Promosi Anda.') }}
        </p>

    </div>
</x-guest-layout>