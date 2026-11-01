@extends('layouts.app')
@section('title', 'Dashboard - Sistem Izin Kerja Sisi Udara')
@section('crumb_parent', 'Administrasi sistem')
@section('crumb', 'Dashboard')

@section('content')
@php
    $authUser = auth()->user();
    // Donut distribusi peran: warna tetap per urutan enum Role, besaran dari data asli.
    $rolePalette = ['#0E7C85', '#18B0B8', '#F8A840', '#0A2A43', '#94A7B8'];
    $roleTotal = max(1, (int) $roleDistribution->sum('count'));
    $donutStops = [];
    $acc = 0;
    foreach ($roleDistribution as $i => $row) {
        $from = $acc / $roleTotal * 100;
        $acc += (int) $row['count'];
        $to = $acc / $roleTotal * 100;
        if ($to > $from) {
            $donutStops[] = ($rolePalette[$i] ?? '#98a2b3') . ' ' . round($from, 3) . '% ' . round($to, 3) . '%';
        }
    }
    $donutStyle = $donutStops ? 'background: conic-gradient(' . implode(', ', $donutStops) . ');' : 'background: #edf0f4;';
    $barTones = ['#0E7C85', '#18B0B8', '#0A2A43', '#F8A840'];
    $kpiTones = ['blue', 'purple', 'green', 'orange'];
    $activities = $recentActivity->take(5);
    $applications = $recentApplications->take(5);
@endphp
<div class="system-admin">

{{-- ---- Heading halaman ------------------------------------------------------- --}}
<div class="dashboard-heading">
    <div class="dashboard-heading-main">
        <div class="eyebrow">ADMINISTRASI SISTEM</div>
        <h1>Dashboard</h1>
        <p>Ringkasan kondisi sistem, konfigurasi, dan aktivitas terbaru.</p>
    </div>
    <div class="dashboard-meta">
        <div class="dashboard-clock" title="Waktu server (Asia/Jakarta)">
            <i class="fa-regular fa-calendar"></i>
            <span class="dashboard-clock-text">
                <strong id="dashboardClockDate" data-timezone="Asia/Jakarta">Memuat tanggal…</strong>
                <small id="dashboardClockTime" data-timezone="Asia/Jakarta">—</small>
            </span>
        </div>
    </div>
</div>

{{-- ---- Kartu ringkasan -------------------------------------------------------- --}}
<section class="metric-grid dashboard-metrics" aria-label="Ringkasan sistem">
    @foreach ($summaryCards as $i => $card)
        <article class="metric-card kpi-{{ $kpiTones[$i] ?? 'blue' }}">
            <div class="metric-icon"><i class="fa-solid {{ $card['icon'] }}"></i></div>
            <div class="metric-content">
                <span class="metric-label">{{ $card['label'] }}</span>
                <strong>{{ $card['count'] }}</strong>
                <small>{{ $card['note'] }}</small>
            </div>
            <span class="kpi-trend" title="{{ $card['trend'] }}% data dibuat dalam 30 hari terakhir"><i class="fa-solid fa-arrow-up"></i> {{ $card['trend'] }}%</span>
        </article>
    @endforeach
</section>

<div class="dashboard-grid dashboard-grid-triple">

    {{-- ---- Distribusi pengguna per peran ------------------------------------- --}}
    <article class="section-card role-panel-card">
        <div class="section-heading"><div><h2>Distribusi pengguna per peran</h2></div></div>
        <div class="sa-donut-wrap">
            <div class="sa-donut" style="{{ $donutStyle }}" role="img" aria-label="Distribusi {{ $roleTotal }} pengguna per peran">
                <div class="sa-donut-center">
                    <strong>{{ $roleTotal }}</strong>
                    <small>Pengguna</small>
                </div>
            </div>
            <ul class="sa-legend">
                @foreach ($roleDistribution as $i => $row)
                    <li>
                        <span class="sa-dot" style="background: {{ $rolePalette[$i] ?? '#98a2b3' }}"></span>
                        <span class="sa-legend-label">{{ $row['label'] }}</span>
                        <strong>{{ $row['count'] }}</strong>
                    </li>
                @endforeach
            </ul>
        </div>
    </article>

    {{-- ---- Status Konfigurasi Sistem ----------------------------------------- --}}
    <article class="section-card role-panel-card">
        <div class="section-heading"><div><h2>Status Konfigurasi Sistem</h2></div></div>
        <div class="sa-config-list">
            @foreach (array_slice($configSummary, 0, 4) as $i => $row)
                @php $pct = $row['total'] > 0 ? round($row['active'] / $row['total'] * 100, 1) : 0; @endphp
                <div class="sa-config-row">
                    <span class="sa-config-label">{{ $row['label'] }}</span>
                    <span class="sa-config-bar"><i style="width: {{ max(0, min(100, $pct)) }}%; background: {{ $barTones[$i] ?? '#0E7C85' }}"></i></span>
                    <strong class="sa-config-value">{{ $row['active'] }} / {{ $row['total'] }}</strong>
                </div>
            @endforeach
        </div>
    </article>

    {{-- ---- Aksi cepat --------------------------------------------------------- --}}
    <article class="section-card role-panel-card">
        <div class="section-heading"><div><h2>Aksi cepat</h2></div></div>
        <div class="sa-quick-grid">
            <a class="sa-quick" href="{{ route('system-admin.users.index') }}">
                <i class="fa-solid fa-user-plus"></i>
                <span><strong>Kelola pengguna</strong><small>Tambah dan kelola akun pengguna sistem</small></span>
                <i class="fa-solid fa-chevron-right sa-quick-arrow"></i>
            </a>
            <a class="sa-quick" href="{{ route('system-admin.roles.index') }}">
                <i class="fa-solid fa-user-shield"></i>
                <span><strong>Kelola peran</strong><small>Atur hak akses sistem</small></span>
                <i class="fa-solid fa-chevron-right sa-quick-arrow"></i>
            </a>
            <a class="sa-quick" href="{{ route('system-admin.areas.index') }}">
                <i class="fa-solid fa-map-location-dot"></i>
                <span><strong>Master data area</strong><small>Kelola area kerja</small></span>
                <i class="fa-solid fa-chevron-right sa-quick-arrow"></i>
            </a>
            <a class="sa-quick" href="{{ route('system-admin.work-types.index') }}">
                <i class="fa-solid fa-briefcase"></i>
                <span><strong>Master data pekerjaan</strong><small>Kelola jenis pekerjaan</small></span>
                <i class="fa-solid fa-chevron-right sa-quick-arrow"></i>
            </a>
        </div>
    </article>
</div>

{{-- ---- Aktivitas sistem terbaru ---------------------------------------------- --}}
<article class="section-card role-panel-card sa-panel">
    <div class="section-heading">
        <div><h2>Aktivitas sistem terbaru</h2><p>{{ $activities->count() }} aktivitas terakhir pada sistem.</p></div>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('system-admin.audit.index') }}">Lihat semua log</a>
    </div>
    <div class="table-responsive role-table-wrap">
        <table class="table role-table sa-table">
            <thead><tr><th>WAKTU (WIB)</th><th>PENGGUNA</th><th>AKTIVITAS</th><th>OBJEK</th><th>DETAIL</th></tr></thead>
            <tbody>
            @forelse ($activities as $log)
                <tr>
                    <td>{{ $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}</td>
                    <td>{{ $log->user_name }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->target ?: '—' }}</td>
                    <td>{{ $log->detail ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">Belum ada aktivitas tercatat.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</article>

<div class="dashboard-grid">
    {{-- ---- Pengajuan terbaru -------------------------------------------------- --}}
    <article class="section-card role-panel-card">
        <div class="section-heading">
            <div><h2>Pengajuan terbaru</h2><p>{{ $applications->count() }} pengajuan izin kerja terbaru pada sistem.</p></div>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('system-admin.index') }}#history">Lihat semua pengajuan</a>
        </div>
        <div class="table-responsive role-table-wrap">
            <table class="table role-table sa-table">
                <thead><tr><th>WAKTU (WIB)</th><th>PEMOHON</th><th>AREA</th><th>JENIS PEKERJAAN</th><th>STATUS</th></tr></thead>
                <tbody>
                @forelse ($applications as $application)
                    <tr>
                        <td>{{ ($application->submitted_at ?? $application->created_at)?->timezone('Asia/Jakarta')?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                        <td><strong>{{ $application->applicant_name ?: $application->contact_name }}</strong></td>
                        <td>{{ $application->area?->name ?? '—' }}</td>
                        <td>{{ $application->workType?->name ?? '—' }}</td>
                        <td>@include('partials.status-badge', ['status' => $application->status])</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">Belum ada pengajuan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>

    {{-- ---- Ringkasan konfigurasi --------------------------------------------- --}}
    <article class="section-card role-panel-card">
        <div class="section-heading"><div><h2>Ringkasan konfigurasi</h2><p>Status dan jumlah data pada sistem.</p></div></div>
        <div class="table-responsive role-table-wrap">
            <table class="table role-table sa-table">
                <thead><tr><th>KATEGORI</th><th>JUMLAH</th><th>AKTIF</th><th>NONAKTIF</th><th>AKSI</th></tr></thead>
                <tbody>
                @foreach ($configSummary as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>{{ $row['active'] ?? '—' }}</td>
                        <td>{{ $row['inactive'] ?? '—' }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('system-admin.' . $row['target'] . '.index') }}">Kelola</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </article>
</div>

</div>

@push('scripts')
{{-- Tanggal dan jam berjalan, konsisten untuk seluruh pengguna (Asia/Jakarta/WIB). --}}
<script>
    (function () {
        var dateEl = document.getElementById('dashboardClockDate');
        var timeEl = document.getElementById('dashboardClockTime');
        if (!dateEl || !timeEl) { return; }
        var tz = dateEl.getAttribute('data-timezone') || 'Asia/Jakarta';
        var dateFmt = new Intl.DateTimeFormat('id-ID', { timeZone: tz, weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
        var timeFmt = new Intl.DateTimeFormat('id-ID', { timeZone: tz, hour: '2-digit', minute: '2-digit', hour12: false });
        var tick = function () {
            var now = new Date();
            dateEl.textContent = dateFmt.format(now);
            timeEl.textContent = timeFmt.format(now).replace('.', ':') + ' WIB';
        };
        tick();
        setInterval(tick, 1000);
    })();
</script>
@endpush
@endsection
