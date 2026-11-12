@extends('layouts.guest')
@section('title', 'Verify email')
@section('content')
    <div class="otp-icon"><i class="bi bi-envelope-check"></i></div>
    <h1 class="h3 mb-2">Verify Your Email Address</h1>
    <p class="text-muted mb-1">We have sent a 6-digit verification code to your email address. Please enter the code below to verify your account.</p>
    <p class="fw-semibold mb-4"><i class="bi bi-envelope me-1 text-muted"></i>{{ $maskedEmail }}</p>

    <form method="POST" action="{{ route('otp.verify') }}" id="otpForm" novalidate>
        @csrf
        <input type="hidden" name="code" id="code">
        <div class="otp-boxes">
            @for($i = 0; $i < 6; $i++)
                <input class="otp-box @error('code') is-invalid @enderror" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" aria-label="Digit {{ $i + 1 }}" {{ $i === 0 ? 'autofocus' : '' }}>
            @endfor
        </div>
        @error('code') <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> @enderror

        <div class="small mt-3 text-muted" id="expiryBox" data-expires="{{ $expiresAt }}">
            @if($expiresAt)<i class="bi bi-hourglass-split me-1"></i>Code expires in <strong id="expiryText">--:--</strong>
            @else<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>No active code. Click "Resend code" below.</span>@endif
        </div>

        <button class="btn btn-primary btn-lg w-100 mt-3">Verify email</button>
    </form>

    <form method="POST" action="{{ route('otp.resend') }}" class="mt-3 text-center">
        @csrf
        <button class="btn btn-light w-100 no-loading" id="resendBtn" data-ready-at="{{ now()->timestamp + $cooldown }}">Resend code</button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        <a href="{{ route('login') }}">Back to login</a> · <a href="{{ route('register') }}">Use a different email</a>
    </p>

    @if($mailIsLocal)
        <div class="alert alert-warning small mt-3 mb-0">
            <strong>Development mode:</strong> the mail driver is <code>{{ config('mail.default') }}</code>, so no real email is sent. The email is written to <code>storage/logs/laravel.log</code>. Configure SMTP in <code>.env</code> to send real emails.
        </div>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    // ----- six digit boxes -> hidden "code" field -----
    const boxes = [...document.querySelectorAll('.otp-box')], hidden = document.getElementById('code');
    const sync = () => hidden.value = boxes.map(b => b.value).join('');
    boxes.forEach((box, i) => {
        box.addEventListener('input', () => { box.value = box.value.replace(/\D/g, '').slice(-1); if (box.value && boxes[i + 1]) boxes[i + 1].focus(); sync(); });
        box.addEventListener('keydown', e => { if (e.key === 'Backspace' && !box.value && boxes[i - 1]) { boxes[i - 1].focus(); } });
        box.addEventListener('paste', e => {
            const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
            if (!digits) return; e.preventDefault();
            digits.split('').forEach((d, k) => boxes[k].value = d);
            boxes[Math.min(digits.length, 5)].focus(); sync();
        });
    });

    // ----- expiry countdown -----
    const box = document.getElementById('expiryBox'), text = document.getElementById('expiryText'), expires = parseInt(box.dataset.expires || 0, 10);
    const pad = n => String(n).padStart(2, '0');
    function tickExpiry() {
        if (!expires) return;
        const left = expires - Math.floor(Date.now() / 1000);
        if (left <= 0) { box.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>This code has expired. Click "Resend code" for a new one.</span>'; return; }
        text.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
        setTimeout(tickExpiry, 1000);
    }
    tickExpiry();

    // ----- resend cooldown -----
    const btn = document.getElementById('resendBtn'), readyAt = parseInt(btn.dataset.readyAt, 10);
    function tickResend() {
        const left = readyAt - Math.floor(Date.now() / 1000);
        if (left > 0) { btn.disabled = true; btn.textContent = 'Resend code in ' + left + 's'; setTimeout(tickResend, 1000); }
        else { btn.disabled = false; btn.textContent = 'Resend code'; }
    }
    tickResend();
})();
</script>
@endpush
