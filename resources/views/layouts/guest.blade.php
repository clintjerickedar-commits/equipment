<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Equipment Borrowing System</title>
    @include('partials.head')
</head>
<body>
<div class="auth-stage">
    @include('partials.tech-bg')

    <div class="auth-center">
        <a href="{{ route('home') }}" class="back-home" aria-label="Back to Home"><i class="bi bi-arrow-left"></i><span>Back to Home</span></a>

        <a href="{{ route('home') }}" class="auth-brand lg"><span class="logo"><i class="bi bi-box-seam"></i></span><span>EquipTrack<small>Borrowing Management</small></span></a>

        <div class="glass-card @yield('card_class')">
            @include('partials.flash')
            @yield('content')
        </div>

        <p class="auth-note"><i class="bi bi-shield-check"></i>@yield('foot_note', 'Secure access to EquipTrack') · © {{ date('Y') }} EquipTrack</p>
    </div>
</div>
@include('partials.scripts')
<script>
    // Show / hide password buttons: <button data-toggle-password="#fieldId">
    document.querySelectorAll('[data-toggle-password]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.querySelector(btn.dataset.togglePassword), show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    }));
</script>
@stack('scripts')
</body>
</html>
