<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Equipment Borrowing System</title>
    @include('partials.head')
</head>
<body>
@php
    $authUser = auth()->user();
    $isAdmin  = $authUser->isAdmin();
    $initials = collect(explode(' ', trim($authUser->name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
@endphp

<aside class="sidebar" id="sidebar">
    <a class="brand" href="{{ route('dashboard') }}">
        <span class="logo"><i class="bi bi-box-seam"></i></span>
        <span><b>EquipTrack</b><small>{{ $isAdmin ? 'Admin Panel' : 'Borrowing Management' }}</small></span>
    </a>

    @if($isAdmin)
        <div class="nav-label">Overview</div>
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
        <div class="nav-label">Inventory</div>
        <a class="nav-link {{ request()->routeIs('admin.equipment.*') ? 'active' : '' }}" href="{{ route('admin.equipment.index') }}"><i class="bi bi-camera-video"></i> Equipment</a>
        <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i> Categories</a>
        <div class="nav-label">Borrowing</div>
        <a class="nav-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}" href="{{ route('admin.requests.index') }}"><i class="bi bi-inbox"></i> Requests</a>
        <a class="nav-link {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}" href="{{ route('admin.returns.index') }}"><i class="bi bi-arrow-return-left"></i> Returns</a>
        <a class="nav-link {{ request()->routeIs('admin.history') ? 'active' : '' }}" href="{{ route('admin.history') }}"><i class="bi bi-clock-history"></i> History</a>
        <div class="nav-label">Administration</div>
        <a class="nav-link {{ request()->routeIs('admin.borrowers.*') ? 'active' : '' }}" href="{{ route('admin.borrowers.index') }}"><i class="bi bi-people"></i> Borrowers</a>
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-person-lock"></i> User accounts</a>
        <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
        <a class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><i class="bi bi-sliders"></i> Settings</a>
    @else
        <div class="nav-label">Menu</div>
        <a class="nav-link {{ request()->routeIs('borrower.dashboard') ? 'active' : '' }}" href="{{ route('borrower.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
        <a class="nav-link {{ request()->routeIs('borrower.equipment') ? 'active' : '' }}" href="{{ route('borrower.equipment') }}"><i class="bi bi-camera-video"></i> Browse equipment</a>
        <a class="nav-link {{ request()->routeIs('borrower.requests.create') ? 'active' : '' }}" href="{{ route('borrower.requests.create') }}"><i class="bi bi-plus-circle"></i> New request</a>
        <a class="nav-link {{ request()->routeIs('borrower.requests.index', 'borrower.requests.show') ? 'active' : '' }}" href="{{ route('borrower.requests.index') }}"><i class="bi bi-inbox"></i> My requests</a>
        <a class="nav-link {{ request()->routeIs('borrower.history') ? 'active' : '' }}" href="{{ route('borrower.history') }}"><i class="bi bi-clock-history"></i> My history</a>
    @endif
    <div class="nav-label">Account</div>
    <a class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" href="{{ route('profile') }}"><i class="bi bi-person-gear"></i> Profile &amp; settings</a>

    <div class="sidebar-foot">
        <a href="{{ route('profile') }}" class="user-chip">
            <span class="avatar">{{ $initials }}</span>
            <span class="text-truncate"><strong class="d-block text-truncate">{{ $authUser->name }}</strong><small>{{ $isAdmin ? 'Administrator' : 'Borrower' }}</small></span>
        </a>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="main">
    <header class="topbar no-print">
        <button class="icon-btn d-lg-none no-loading" id="sidebarToggle" type="button" aria-label="Open menu"><i class="bi bi-list fs-5"></i></button>
        <div class="crumb">@yield('title', 'Dashboard')<small class="d-none d-sm-block">{{ now()->format('l, F j, Y') }}</small></div>

        <div class="ms-auto d-flex align-items-center gap-2">
            @php($notif = $notifications ?? ['items' => collect(), 'count' => 0])
            <div class="dropdown">
                <button class="icon-btn position-relative no-loading" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    @if($notif['count'] > 0)<span class="notif-badge">{{ $notif['count'] > 9 ? '9+' : $notif['count'] }}</span>@endif
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-menu">
                    <div class="head"><span>Notifications</span><span class="badge-soft {{ $notif['count'] > 0 ? 'b-pending' : 'b-cancelled' }}">{{ $notif['count'] }}</span></div>
                    @forelse($notif['items'] as $n)
                        <a href="{{ $n['url'] }}" class="item">
                            <span class="dot tone-{{ $n['tone'] }}"><i class="bi bi-{{ $n['icon'] }}"></i></span>
                            <div class="min-w-0"><div class="fw-semibold text-truncate">{{ $n['title'] }}</div><small>{{ $n['text'] }} · {{ $n['time']->diffForHumans() }}</small></div>
                        </a>
                    @empty
                        <x-empty-state icon="bell-slash" message="You are all caught up." />
                    @endforelse
                </div>
            </div>

            <div class="dropdown">
            <button class="profile-btn no-loading" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar sm">{{ $initials }}</span>
                <span class="name d-none d-sm-block">{{ \Illuminate\Support\Str::limit($authUser->name, 18) }}<small>{{ $isAdmin ? 'Administrator' : 'Borrower' }}</small></span>
                <i class="bi bi-chevron-down small text-muted"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:220px">
                <li class="px-3 py-2"><div class="fw-semibold">{{ $authUser->name }}</div><div class="small text-muted">{{ $authUser->email }}</div></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person-gear me-2"></i>Profile &amp; settings</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="dropdown-item text-danger no-loading"><i class="bi bi-box-arrow-right me-2"></i>Log out</button>
                    </form>
                </li>
            </ul>
            </div>
        </div>
    </header>

    <main class="content">
        @include('partials.flash')
        @yield('content')
    </main>
</div>

@include('partials.confirm-modal')
@include('partials.scripts')
@stack('scripts')
</body>
</html>
