<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Equipment Borrowing System · Borrow Smarter. Manage Better.</title>
    <script>document.documentElement.classList.add('js');</script>
    @include('partials.head')
</head>
<body class="lp-body">

{{-- ===== Navigation (sticky) ===== --}}
<nav class="navbar navbar-expand-lg lp-nav">
    <div class="container">
        <a class="navbar-brand" href="#top"><span class="logo"><i class="bi bi-box-seam"></i></span><span>EquipTrack</span></a>
        <button class="navbar-toggler no-loading" type="button" data-bs-toggle="collapse" data-bs-target="#lpNav" aria-controls="lpNav" aria-expanded="false" aria-label="Menu"><i class="bi bi-list fs-4"></i></button>
        <div class="collapse navbar-collapse" id="lpNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#highlights">Highlights</a></li>
            </ul>
            <div class="nav-actions d-flex gap-2 align-items-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i> Go to dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost-red"><i class="bi bi-box-arrow-in-right"></i> Log In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Create Account</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- ===== Hero ===== --}}
<header class="lp-hero" id="top">
    <div class="lp-particles" aria-hidden="true">
        @for($i = 0; $i < 22; $i++)
            <span class="lp-dot" style="left: {{ ($i * 37 + 11) % 100 }}%; top: {{ ($i * 53 + 7) % 100 }}%; width: {{ 2 + ($i % 3) }}px; height: {{ 2 + ($i % 3) }}px; animation-delay: -{{ $i % 9 }}s; animation-duration: {{ 7 + ($i % 5) * 2 }}s"></span>
        @endfor
        <span class="lp-geo g1"></span><span class="lp-geo g2"></span><span class="lp-geo g3"></span>
    </div>

    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 lp-copy">
                <span class="lp-pill"><i class="bi bi-stars"></i> Equipment Borrowing Management System</span>
                <h1>Borrow <em>Smarter.</em><br>Manage <em>Better.</em></h1>
                <p class="lp-lead">A single platform to browse available equipment, submit borrowing requests, track approvals, and manage return deadlines efficiently. Built for students, staff, and the equipment office.</p>
                <div class="d-flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg btn-cta"><i class="bi bi-speedometer2"></i> Go to dashboard <i class="bi bi-arrow-right"></i></a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg btn-cta"><i class="bi bi-person-plus"></i> Create Account <i class="bi bi-arrow-right"></i></a>
                        <a href="{{ route('login') }}" class="btn btn-ghost-red btn-lg lp-btn-ghost"><i class="bi bi-box-arrow-in-right"></i> Log In</a>
                    @endauth
                </div>
            </div>

            <div class="col-lg-6">
                <div class="lp-visual">
                    @include('partials.illustration', ['pills' => false])

                    <div class="lp-float f1"><span class="ico tone-green"><i class="bi bi-check2-circle"></i></span><div><b>Approved</b><small>Your request was approved</small></div></div>
                    <div class="lp-float f2"><span class="ico tone-amber"><i class="bi bi-hourglass-split"></i></span><div><b>Pending Request</b><small>Waiting for review</small></div></div>
                    <div class="lp-float f3"><span class="ico tone-red"><i class="bi bi-alarm"></i></span><div><b>Due Soon</b><small>Return in 3 days</small></div></div>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- ===== Features ===== --}}
<section class="lp-section" id="features">
    <div class="container">
        <div class="lp-head" data-reveal>
            <span class="lp-kicker">Features</span>
            <h2>Everything you need to <em>borrow with confidence</em></h2>
            <p>From the first search to the final return, each step is clear, tracked and organized.</p>
        </div>
        <div class="row g-4">
            @foreach([
                ['search',        'Browse Available Equipment',  'View available equipment and quickly search and filter by category.'],
                ['send-check',    'Easy Borrowing Requests',     'Submit borrowing requests and select your borrowing and return dates.'],
                ['activity',      'Real-Time Request Tracking',  'Track pending, approved, rejected and cancelled requests at a glance.'],
                ['alarm',         'Return Deadline Monitoring',  'Monitor return dates, with upcoming deadlines and overdue equipment highlighted.'],
                ['box-seam',      'Equipment Management',        'Manage equipment information, stock and availability in one place.'],
                ['grid-1x2',      'User-Friendly Dashboard',     'An organized dashboard for both borrowers and administrators.'],
            ] as $i => [$icon, $title, $text])
                <div class="col-md-6 col-xl-4" data-reveal style="--d: {{ ($i % 3) * .08 }}s">
                    <div class="lp-card">
                        <span class="lp-ico"><i class="bi bi-{{ $icon }}"></i></span>
                        <h5>{{ $title }}</h5>
                        <p>{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== How it works ===== --}}
<section class="lp-section alt" id="how">
    <div class="container">
        <div class="lp-head" data-reveal>
            <span class="lp-kicker">How It Works</span>
            <h2>Six simple steps from <em>request to return</em></h2>
        </div>
        <div class="lp-steps">
            @foreach([
                ['person-plus',       'Create an Account',               'Register with your email and verify it with a 6-digit code.'],
                ['search',            'Browse Available Equipment',      'Check live stock and filter by category.'],
                ['send',              'Submit Borrowing Request',        'Pick items, quantities, dates and a purpose.'],
                ['hourglass-split',   'Wait for Administrator Approval', 'The equipment office approves or replies with a reason.'],
                ['box-arrow-up-right','Borrow Equipment',                'Collect your approved items.'],
                ['arrow-return-left', 'Return Equipment on Time',        'Bring everything back by the return date.'],
            ] as $i => [$icon, $title, $text])
                <div class="lp-step" data-reveal style="--d: {{ $i * .07 }}s">
                    <span class="lp-num">{{ $i + 1 }}</span>
                    <i class="bi bi-{{ $icon }} lp-step-ico"></i>
                    <h6>{{ $title }}</h6>
                    <p>{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== Highlights ===== --}}
<section class="lp-section" id="highlights">
    <div class="container">
        <div class="lp-head" data-reveal>
            <span class="lp-kicker">Highlights</span>
            <h2>Why teams choose <em>EquipTrack</em></h2>
            <p>Live numbers from the system, and the benefits you get from day one.</p>
        </div>

        {{-- real numbers from the database --}}
        <div class="lp-stats" data-reveal>
            <div class="lp-stat"><b data-count="{{ $highlights['units'] }}">{{ number_format($highlights['units']) }}</b><span>units ready to borrow</span></div>
            <div class="lp-stat"><b data-count="{{ $highlights['categories'] }}">{{ number_format($highlights['categories']) }}</b><span>equipment categories</span></div>
            <div class="lp-stat"><b data-count="{{ $highlights['borrowers'] }}">{{ number_format($highlights['borrowers']) }}</b><span>registered borrowers</span></div>
            <div class="lp-stat"><b data-count="{{ $highlights['returned'] }}">{{ number_format($highlights['returned']) }}</b><span>borrowings completed</span></div>
        </div>

        <div class="row g-4 mt-1">
            @foreach([
                ['journal-check',      'Organized Equipment Records', 'Every item, quantity and condition kept in one clean inventory.'],
                ['lightning-charge',   'Faster Borrowing Process',    'Request in minutes and get an answer without queues.'],
                ['eye',                'Easy Request Monitoring',     'See the status of every request, from pending to returned.'],
                ['sliders',            'Better Equipment Management', 'Stock, availability and condition update automatically.'],
                ['file-earmark-check', 'Reduced Manual Paperwork',    'Replace paper forms and logbooks with a single system.'],
                ['calendar-check',     'Efficient Return Monitoring', 'Deadlines and overdue items are flagged for you.'],
            ] as $i => [$icon, $title, $text])
                <div class="col-md-6 col-xl-4" data-reveal style="--d: {{ ($i % 3) * .08 }}s">
                    <div class="lp-hl">
                        <span class="lp-ico sm"><i class="bi bi-{{ $icon }}"></i></span>
                        <div><h6>{{ $title }}</h6><p>{{ $text }}</p></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== Call to action ===== --}}
<section class="lp-section pt-0">
    <div class="container">
        <div class="lp-cta" data-reveal>
            <span class="lp-geo c1"></span><span class="lp-geo c2"></span>
            <h2>Ready to Borrow Smarter?</h2>
            <p>Experience a faster and more organized way to borrow and manage school equipment with EquipTrack.</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="btn btn-lp-light btn-lg"><i class="bi bi-rocket-takeoff me-1"></i> Get Started</a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-lp-outline btn-lg"><i class="bi bi-box-arrow-in-right me-1"></i> Log In</a>
                @endguest
            </div>
        </div>
    </div>
</section>

{{-- ===== Footer ===== --}}
@php
    $contactEmail = config('equiptrack.contact_email');
    $showEmail = $contactEmail && !str_contains($contactEmail, 'example.com');
@endphp
<footer class="lp-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a class="navbar-brand lp-foot-brand" href="#top"><span class="logo"><i class="bi bi-box-seam"></i></span><span>EquipTrack</span></a>
                <p class="mt-3">A modern equipment borrowing management system for schools: browse equipment, send requests, follow approvals and never miss a return deadline.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Quick Links</h6>
                <ul>
                    <li><a href="#top">Home</a></li>
                    <li><a href="{{ route('login') }}">Log In</a></li>
                    <li><a href="{{ route('register') }}">Create Account</a></li>
                    <li><a href="{{ route('admin.login') }}">Administrator</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Features</h6>
                <ul>
                    <li><a href="#features">Browse Equipment</a></li>
                    <li><a href="#features">Borrowing Requests</a></li>
                    <li><a href="#features">Request Tracking</a></li>
                    <li><a href="#how">How It Works</a></li>
                    <li><a href="#highlights">Highlights</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6>Contact Information</h6>
                <ul class="contact">
                    @if(config('equiptrack.contact_address'))<li><i class="bi bi-geo-alt"></i> {{ config('equiptrack.contact_address') }}</li>@endif
                    @if($showEmail)<li><i class="bi bi-envelope"></i> <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></li>@endif
                    @if(config('equiptrack.contact_phone'))<li><i class="bi bi-telephone"></i> {{ config('equiptrack.contact_phone') }}</li>@endif
                </ul>
            </div>
        </div>
        <div class="lp-copy-line">Copyright © {{ date('Y') }} EquipTrack. All Rights Reserved.</div>
    </div>
</footer>

@include('partials.scripts')
<script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
