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

    <div class="auth-split">
        {{-- LEFT: branding --}}
        <section class="auth-left">
            <a href="{{ route('home') }}" class="auth-brand"><span class="logo"><i class="bi bi-box-seam"></i></span><span>EquipTrack<small>Borrowing Management</small></span></a>

            <div class="auth-left-main">
                <div class="hero-icon"><i class="bi bi-{{ trim($__env->yieldContent('side_icon')) }}"></i></div>
                <div class="wordmark">Equip<span>Track</span></div>
                <div class="role-label">@yield('role_label')</div>
                <p class="role-sub">@yield('side_text')</p>
                <div class="chips">@yield('side_chips')</div>
            </div>

            <div class="auth-foot"><span>© {{ date('Y') }} EquipTrack</span><span><i class="bi bi-shield-check"></i>@yield('foot_note', 'Secure sign-in')</span></div>
        </section>

        {{-- RIGHT: Back to Home + glass login card --}}
        <section class="auth-right">
            <a href="{{ route('home') }}" class="back-home" aria-label="Back to Home"><i class="bi bi-arrow-left"></i><span>Back to Home</span></a>
            <div class="glass-card">
                @include('partials.flash')
                @yield('content')
            </div>
            <p class="auth-note d-lg-none"><i class="bi bi-shield-check"></i>@yield('foot_note', 'Secure sign-in')</p>
        </section>
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
