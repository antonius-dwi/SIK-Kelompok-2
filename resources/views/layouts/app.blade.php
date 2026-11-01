@php
    $authUser = auth()->user();
    $role = $authUser->role;
    $navCount = \App\Support\NavCounts::for($authUser);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem pengajuan izin kerja dan pengendalian barang serta peralatan di sisi udara">
    <title>@yield('title', 'Sistem Izin Kerja Sisi Udara')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Versi mengikuti mtime berkas agar browser tidak memakai cache lama setelah deploy. --}}
    <link href="{{ asset('css/sik.css') }}?v={{ @filemtime(public_path('css/sik.css')) ?: '1' }}" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    @stack('head')
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sikSidebar" aria-label="Navigasi utama">
        <a class="brand brand-logo" href="{{ route('home') }}">
            {{-- Banner gabungan SIK + InJourney Airports (turunan dari Modern Airport Corporate Logo Banner.png). --}}
            <span class="brand-logo-panel">
                <img src="{{ asset('images/sidebar-banner.png') }}?v={{ filemtime(public_path('images/sidebar-banner.png')) }}"
                     alt="SIK — InJourney Airports" width="900" height="302">
            </span>
        </a>
        @unless ($role === \App\Enums\Role::SystemAdmin)
            <div class="sidebar-label">RUANG KERJA</div>
        @endunless
        <nav class="side-nav {{ $role === \App\Enums\Role::SystemAdmin ? 'side-nav-compact' : '' }}" id="roleNavigation">
            @if ($role === \App\Enums\Role::Applicant)
                <a class="side-link {{ request()->routeIs('applicant.dashboard') ? 'active' : '' }}" href="{{ route('applicant.dashboard') }}"><i class="fa-solid fa-table-columns"></i><span>Beranda</span></a>
                <a class="side-link {{ request()->routeIs('applicant.applications.create', 'applicant.applications.edit') ? 'active' : '' }}" href="{{ route('applicant.applications.create') }}"><i class="fa-regular fa-file-lines"></i><span>Pengajuan baru</span></a>
                <a class="side-link" href="{{ route('applicant.dashboard') }}#my-applications"><i class="fa-solid fa-timeline"></i><span>Pengajuan saya</span></a>
            @elseif ($role === \App\Enums\Role::Admin)
                <a class="side-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.queues') }}"><i class="fa-solid fa-list-check"></i><span>Antrean pemeriksaan</span><span class="nav-count">{{ $navCount }}</span></a>
            @elseif ($role === \App\Enums\Role::Approver)
                <a class="side-link {{ request()->routeIs('approver.*') ? 'active' : '' }}" href="{{ route('approver.queue') }}"><i class="fa-solid fa-list-check"></i><span>Antrean persetujuan</span><span class="nav-count">{{ $navCount }}</span></a>
            @elseif ($role === \App\Enums\Role::Management)
                <a class="side-link {{ request()->routeIs('management.*') ? 'active' : '' }}" href="{{ route('management.index') }}"><i class="fa-solid fa-chart-column"></i><span>Pemantauan</span></a>
            @elseif ($role === \App\Enums\Role::SystemAdmin)
                <a class="side-link {{ request()->routeIs('system-admin.dashboard') ? 'active' : '' }}" href="{{ route('system-admin.dashboard') }}"><i class="fa-solid fa-house"></i><span>Dashboard</span></a>
                <div class="sidebar-label sidebar-label-spaced">PENGELOLAAN SISTEM</div>
                <a class="side-link {{ request()->routeIs('system-admin.recap.*') ? 'active' : '' }}" href="{{ route('system-admin.recap.index') }}"><i class="fa-solid fa-file-export"></i><span>Rekap SIK</span></a>
                <a class="side-link {{ request()->routeIs('system-admin.users.*') ? 'active' : '' }}" href="{{ route('system-admin.users.index') }}"><i class="fa-solid fa-users"></i><span>Pengguna</span></a>
                <a class="side-link {{ request()->routeIs('system-admin.roles.*') ? 'active' : '' }}" href="{{ route('system-admin.roles.index') }}"><i class="fa-solid fa-user-shield"></i><span>Peran</span></a>
                <a class="side-link {{ request()->routeIs('system-admin.departments.*') ? 'active' : '' }}" href="{{ route('system-admin.departments.index') }}"><i class="fa-solid fa-building"></i><span>Departemen</span></a>
                <div class="sidebar-label sidebar-label-spaced">MASTER DATA</div>
                <a class="side-link {{ request()->routeIs('system-admin.areas.*') ? 'active' : '' }}" href="{{ route('system-admin.areas.index') }}"><i class="fa-solid fa-cube"></i><span>Area kerja</span></a>
                <a class="side-link {{ request()->routeIs('system-admin.work-types.*') ? 'active' : '' }}" href="{{ route('system-admin.work-types.index') }}"><i class="fa-solid fa-briefcase"></i><span>Jenis pekerjaan</span></a>
                <div class="sidebar-label sidebar-label-spaced">KONFIGURASI WORKFLOW</div>
                <a class="side-link {{ request()->routeIs('system-admin.workflows.*') ? 'active' : '' }}" href="{{ route('system-admin.workflows.index') }}"><i class="fa-solid fa-arrows-rotate"></i><span>Template alur kerja</span></a>
                <a class="side-link {{ request()->routeIs('system-admin.steps.*') ? 'active' : '' }}" href="{{ route('system-admin.steps.index') }}"><i class="fa-solid fa-stairs"></i><span>Langkah persetujuan</span></a>
                <div class="sidebar-label sidebar-label-spaced">AUDIT</div>
                <a class="side-link {{ request()->routeIs('system-admin.audit.*') ? 'active' : '' }}" href="{{ route('system-admin.audit.index') }}"><i class="fa-solid fa-clock-rotate-left"></i><span>Log aktivitas</span></a>
            @endif
        </nav>
        <div class="sidebar-bottom">
            <div class="airport-card"><i class="fa-solid fa-location-dot"></i><span><strong>Bandara Ahmad Yani</strong><small>Semarang, Indonesia</small></span></div>
            <div class="sidebar-user"><div class="avatar">{{ $authUser->initials() }}</div><span><strong>{{ $authUser->name }}</strong><small>{{ $role->label() }}</small></span></div>
        </div>
    </aside>

    <main class="main-panel">
        <header class="topbar">
            <button class="btn icon-button sidebar-toggle" id="sidebarToggle" type="button" aria-label="Buka atau tutup navigasi"><i class="fa-solid fa-bars"></i></button>
            <div class="crumbs"><span class="crumb-root">Ruang kerja</span><i class="fa-solid fa-chevron-right crumb-root"></i>@hasSection('crumb_parent')<span>@yield('crumb_parent')</span><i class="fa-solid fa-chevron-right"></i>@endif<strong>@yield('crumb', 'Beranda')</strong></div>
            <div class="topbar-actions">
                <div class="dropdown">
                    <button class="btn icon-button notification-button dropdown-toggle" id="notificationToggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi" data-url="{{ route('notifications.index') }}" data-read-url="{{ route('notifications.read') }}"><i class="fa-regular fa-bell"></i><span id="notificationIndicator" hidden></span></button>
                    <div class="dropdown-menu dropdown-menu-end notification-menu" id="notificationMenu" aria-labelledby="notificationToggle"></div>
                </div>
                <div class="top-avatar">{{ $authUser->initials() }}</div>
                <div class="top-user"><strong>{{ $authUser->name }}</strong><small>{{ $role->label() }}</small></div>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary" type="submit">Keluar</button></form>
            </div>
        </header>

        <div class="content-wrap">
            @if (session('status'))
                <div class="py-2 alert alert-success" role="status">{{ session('status') }}</div>
            @endif
            {{-- $errors di-share middleware ShareErrorsFromSession; guard agar layout tetap bisa
                 dirender di luar request web (mis. render snapshot/CLI) tanpa fatal error. --}}
            @if (isset($errors) && $errors->any())
                <div class="py-2 alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <footer class="page-footer"><span>© {{ now()->year }} Sistem Izin Kerja Sisi Udara</span><span>Bandara Internasional Jenderal Ahmad Yani · Semarang</span></footer>
        </div>
    </main>
</div>
@stack('modals')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/sik.js') }}?v={{ @filemtime(public_path('js/sik.js')) ?: '1' }}"></script>
@stack('scripts')
</body>
</html>
