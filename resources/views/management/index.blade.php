@extends('layouts.app')
@section('title', 'Pemantauan - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Pemantauan')

@section('content')
@php $maxMonthly = max(1, $monthly->max('count')); @endphp
<div class="page-heading role-heading"><div><div class="eyebrow">DASHBOARD MANAJEMEN</div><h1>Pemantauan operasional SIK</h1><p>Ringkasan dan daftar pengajuan bersifat hanya-baca.</p></div></div>

<form method="get" action="{{ route('management.index') }}" class="section-card role-panel-card management-filters">
    <label class="filter-field">Area
        <select class="form-select form-select-sm" name="area" onchange="this.form.submit()">
            <option value="">Semua area</option>
            @foreach ($areas as $area)<option value="{{ $area->id }}" @selected((string) ($filters['area'] ?? '') === (string) $area->id)>{{ $area->name }}</option>@endforeach
        </select>
    </label>
    <label class="filter-field">Jenis pekerjaan
        <select class="form-select form-select-sm" name="workType" onchange="this.form.submit()">
            <option value="">Semua jenis</option>
            @foreach ($workTypes as $type)<option value="{{ $type->id }}" @selected((string) ($filters['workType'] ?? '') === (string) $type->id)>{{ $type->name }}</option>@endforeach
        </select>
    </label>
    <label class="filter-field">Status
        <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>@endforeach
        </select>
    </label>
    <label class="filter-field">Dari tanggal<input class="form-control form-control-sm" type="date" name="dateFrom" value="{{ $filters['dateFrom'] ?? '' }}" onchange="this.form.submit()"></label>
    <label class="filter-field">Sampai tanggal<input class="form-control form-control-sm" type="date" name="dateTo" value="{{ $filters['dateTo'] ?? '' }}" onchange="this.form.submit()"></label>
</form>

<div class="management-grid">
    <article class="section-card role-panel-card chart-card">
        <div class="section-heading"><div><div class="section-kicker">VOLUME BULANAN</div><h2>Pengajuan 6 bulan terakhir</h2></div></div>
        <div class="bar-chart" role="img" aria-label="Grafik jumlah pengajuan per bulan">
            @foreach ($monthly as $month)
                <div><span style="--bar-height: {{ max(6, $month['count'] / $maxMonthly * 100) }}%" title="{{ $month['count'] }} pengajuan"></span><small>{{ $month['label'] }}</small></div>
            @endforeach
        </div>
    </article>
    <article class="section-card role-panel-card chart-card">
        <div class="section-heading"><div><div class="section-kicker">STATUS PENGAJUAN</div><h2>Distribusi status</h2></div></div>
        <div class="status-stat-list">
            @foreach ($distribution as $row)
                <div><span>{{ $row['status']->label() }}</span><strong>{{ $row['count'] }}</strong><i style="--stat-width: {{ $row['percent'] }}%"></i></div>
            @endforeach
        </div>
    </article>
</div>

<div class="section-card role-panel-card">
    <div class="section-heading"><div><div class="section-kicker">MONITORING READ-ONLY</div><h2>SIK &amp; barang/peralatan</h2><p>Data terfilter sesuai area, jenis pekerjaan, status, dan rentang tanggal.</p></div></div>
    <div class="table-responsive role-table-wrap"><table class="table role-table">
        <thead><tr><th>NOMOR SIK</th><th>PEMOHON / PEKERJAAN</th><th>AREA</th><th>JENIS</th><th>STATUS</th><th>BARANG</th><th>TANGGAL</th><th>DETAIL</th></tr></thead>
        <tbody>
        @forelse ($applications as $application)
            <tr>
                <td>{{ $application->permit_number ?: $application->number }}</td>
                <td><strong>{{ $application->workType?->name ?? '(belum diisi)' }}</strong><span class="table-subtext">{{ $application->applicant_name }}</span></td>
                <td>{{ $application->area?->name }}</td>
                <td>{{ $application->workType?->name }}</td>
                <td>@include('partials.status-badge', ['status' => $application->status])</td>
                <td>{{ $application->items->isEmpty() ? '0 item' : $application->itemsSummary() }}</td>
                <td>{{ $application->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</td>
                <td><a class="btn btn-sm btn-outline-secondary" href="{{ route('management.show', $application) }}">Lihat detail</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty-state">Tidak ada data untuk filter yang dipilih.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $applications->links() }}
</div>
@endsection
