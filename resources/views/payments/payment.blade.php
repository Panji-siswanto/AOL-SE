@extends('layouts.app')

@section('title', 'Pembayaran - Lapak.in')

@push('styles')
    @vite('resources/css/payment.css')
@endpush

@section('content')

{{-- Countdown Banner --}}
<div class="countdown-banner">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock countdown-icon"></i>
                <span class="countdown-label">Selesaikan pembayaran dalam</span>
            </div>
            <span class="countdown-timer" id="countdown">24:59:50</span>
        </div>
    </div>
</div>

<div class="container payment-container">
    <div class="row g-4">

        {{-- Left Column: Payment Method --}}
        <div class="col-lg-7">

            {{-- Payment Method Card --}}
            <div class="payment-card">
                <div class="payment-card-header">
                    <i class="bi bi-credit-card-2-front-fill header-icon"></i>
                    <h5 class="mb-0 fw-semibold">Pilih Metode Pembayaran</h5>
                </div>

                <div class="payment-methods">

                    {{-- Go-Pay --}}
                    <label class="payment-method-item" for="gopay">
                        <div class="method-icon method-icon--blue">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div class="method-info">
                            <span class="method-name">Go-Pay</span>
                            <span class="method-type">E-Wallet</span>
                        </div>
                        <input class="method-radio" type="radio" name="payment" id="gopay" value="gopay">
                        <span class="custom-radio"></span>
                    </label>

                    {{-- OVO --}}
                    <label class="payment-method-item" for="ovo">
                        <div class="method-icon method-icon--purple">
                            <i class="bi bi-wallet-fill"></i>
                        </div>
                        <div class="method-info">
                            <span class="method-name">OVO</span>
                            <span class="method-type">E-Wallet</span>
                        </div>
                        <input class="method-radio" type="radio" name="payment" id="ovo" value="ovo">
                        <span class="custom-radio"></span>
                    </label>

                    {{-- BCA Virtual Account --}}
                    <label class="payment-method-item" for="bca">
                        <div class="method-icon method-icon--orange">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="method-info">
                            <span class="method-name">BCA Virtual Account</span>
                            <span class="method-type">Bank Transfer</span>
                        </div>
                        <input class="method-radio" type="radio" name="payment" id="bca" value="bca">
                        <span class="custom-radio"></span>
                    </label>

                    {{-- Mandiri Virtual Account --}}
                    <label class="payment-method-item" for="mandiri">
                        <div class="method-icon method-icon--yellow">
                            <i class="bi bi-building-fill"></i>
                        </div>
                        <div class="method-info">
                            <span class="method-name">Mandiri Virtual Account</span>
                            <span class="method-type">Bank Transfer</span>
                        </div>
                        <input class="method-radio" type="radio" name="payment" id="mandiri" value="mandiri">
                        <span class="custom-radio"></span>
                    </label>

                </div>

                <div class="security-badge">
                    <i class="bi bi-shield-check"></i>
                    <span>Pembayaran terenkripsi & aman oleh Lapak.in Security</span>
                </div>
            </div>

        </div>

        {{-- Right Column: Order Summary --}}
        <div class="col-lg-5">
            <div class="summary-card">
                <h5 class="fw-semibold mb-4">Ringkasan Sewa</h5>

                <div class="property-item">
                    <img
                        src="{{ asset('images/ruko-sudirman.jpg') }}"
                        alt="Lahan Ruko Sudirman"
                        class="property-thumb"
                        onerror="this.src='https://placehold.co/80x80/f5f5f5/999?text=Lahan'"
                    >
                    <div class="property-info">
                        <span class="property-name">Lahan Ruko Sudirman</span>
                        <span class="property-loc">Jakarta Barat &bull; 5×15m</span>
                        <span class="property-badge">Sewa 1 bulan</span>
                    </div>
                </div>

                <hr class="summary-divider">

                <div class="summary-row">
                    <span>Harga Sewa</span>
                    <span>Rp 500,000</span>
                </div>
                <div class="summary-row">
                    <span>Biaya Admin</span>
                    <span>Rp 10,000</span>
                </div>
                <div class="summary-row">
                    <span>Deposit</span>
                    <span>Rp 100,000</span>
                </div>

                <hr class="summary-divider">

                <div class="summary-row summary-total">
                    <span>Total Pembayaran</span>
                    <span class="total-amount">Rp 610,000</span>
                </div>

                <button class="btn-pay" id="btnPay">
                    Bayar Sekarang
                </button>
            </div>
        </div>

    </div>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content success-modal-content">
            <div class="modal-body text-center p-5">
                <div class="success-icon-wrap mb-4">
                    <svg class="success-checkmark" viewBox="0 0 52 52" xmlns="http://www.w3.org/2000/svg">
                        <circle class="check-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="check-tick" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h4 class="fw-bold mb-4">Pembayaran Berhasil</h4>
                <button type="button" class="btn-ok" data-bs-dismiss="modal" onclick="window.location.href='{{ route('home') }}'">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Countdown timer
(function () {
    let total = 24 * 3600 + 59 * 60 + 50;
    const el = document.getElementById('countdown');

    const tick = () => {
        if (total <= 0) { el.textContent = '00:00:00'; return; }
        total--;
        const h = String(Math.floor(total / 3600)).padStart(2, '0');
        const m = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
        const s = String(total % 60).padStart(2, '0');
        el.textContent = `${h}:${m}:${s}`;
        setTimeout(tick, 1000);
    };
    setTimeout(tick, 1000);
})();

// Payment method selection highlight
document.querySelectorAll('.method-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.payment-method-item').forEach(item => item.classList.remove('active'));
        if (radio.checked) radio.closest('.payment-method-item').classList.add('active');
    });
});

// Pay button → show modal
document.getElementById('btnPay').addEventListener('click', () => {
    const selected = document.querySelector('.method-radio:checked');
    if (!selected) {
        alert('Pilih metode pembayaran terlebih dahulu.');
        return;
    }
    const modal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
    modal.show();
});
</script>
@endpush
