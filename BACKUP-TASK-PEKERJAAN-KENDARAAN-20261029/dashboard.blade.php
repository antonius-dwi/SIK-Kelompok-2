@extends('layouts.app')
@section('title', 'Beranda - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Beranda')

@section('content')
@php
    $now = now('Asia/Jakarta');
    $hour = (int) $now->format('H');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<section class="page-heading" id="dashboard">
    <div>
        <div class="eyebrow">{{ mb_strtoupper($now->translatedFormat('l, d F Y')) }} · {{ $now->format('H:i') }} WIB <span class="eyebrow-dot"></span> OPERASIONAL SISI UDARA</div>
        <h1>{{ $greeting }}, {{ auth()->user()->name }}</h1>
        <p>{{ $needsAction->count() ? $needsAction->count() . ' pengajuan perlu tindakan Anda.' : 'Tidak ada pengajuan yang perlu tindakan.' }}</p>
    </div>
    <a class="btn btn-primary primary-action" href="{{ route('applicant.applications.create') }}"><i class="fa-solid fa-plus me-2"></i>Pengajuan baru</a>
</section>

<section class="metric-grid" aria-label="Ringkasan pengajuan">
    <article class="metric-card"><div class="metric-icon"><i class="fa-solid fa-folder-open"></i></div><div class="metric-content"><span class="metric-label">Total pengajuan</span><strong>{{ $totalCount }}</strong><small>Semua status</small></div></article>
    <article class="metric-card"><div class="metric-icon"><i class="fa-solid fa-clock"></i></div><div class="metric-content"><span class="metric-label">Dalam proses</span><strong>{{ $inProgressCount }}</strong><small>Di seluruh tahap</small></div></article>
    <article class="metric-card"><div class="metric-icon"><i class="fa-solid fa-circle-check"></i></div><div class="metric-content"><span class="metric-label">Disetujui</span><strong>{{ $approvedCount }}</strong><small>Siap diterbitkan / terbit</small></div></article>
    <article class="metric-card"><div class="metric-icon"><i class="fa-solid fa-boxes-stacked"></i></div><div class="metric-content"><span class="metric-label">Data peralatan</span><strong>{{ $itemCount }}</strong><small>Seluruh pengajuan</small></div></article>
</section>

<div class="action-need-panel {{ $needsAction->isEmpty() ? 'empty' : '' }}">
    @if ($needsAction->isEmpty())
        Tidak ada pengajuan yang perlu tindakan.
    @else
        <div class="fw-semibold" style="font-size:11px; color:#475467;">Perlu tindakan Anda</div>
        <div class="action-need-list">
            @foreach ($needsAction as $item)
                <a class="action-need-item" href="{{ route('applicant.applications.edit', $item) }}"><span class="badge-dot"></span><span>{{ $item->status->label() }} · {{ $item->number }}</span></a>
            @endforeach
        </div>
    @endif
</div>

<section class="status-summary-grid" aria-label="Ringkasan status pengajuan">
    @foreach ($statuses as $status)
        <div><span class="badge status-badge {{ $status->cssClass() }}"><span></span>{{ $status->label() }}</span><strong>{{ $statusCounts[$status->value] ?? 0 }}</strong></div>
    @endforeach
</section>

<section class="section-card role-panel" id="my-applications">
    <div class="section-heading">
        <div><div class="section-kicker">DASHBOARD PEMOHON</div><h2>Pengajuan saya</h2><p>Pantau status permohonan izin kerja dan riwayat prosesnya.</p></div>
        <form method="get" action="{{ route('applicant.dashboard') }}#my-applications">
            <label class="filter-field">Filter status
                <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                    <option value="all">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($filter === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </label>
        </form>
    </div>
    <div class="table-responsive role-table-wrap">
        <table class="table role-table">
            <thead><tr><th>NOMOR SIK</th><th>PEKERJAAN</th><th>AREA</th><th>TANGGAL PENGAJUAN</th><th>STATUS</th><th></th></tr></thead>
            <tbody>
            @forelse ($applications as $application)
                <tr>
                    <td>{{ $application->permit_number ?: $application->number }}</td>
                    <td><strong>{{ $application->job_title ?: '(belum diisi)' }}</strong><span class="table-subtext">{{ $application->applicant_name }}</span></td>
                    <td>{{ $application->area?->name ?? '—' }}</td>
                    <td>{{ $application->updated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</td>
                    <td>@include('partials.status-badge', ['status' => $application->status])</td>
                    <td>
                        @if ($application->status === \App\Enums\ApplicationStatus::Issued)
                            <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="modal" data-bs-target="#permitModal"
                                    data-permit-src="{{ route('applicant.applications.permit', ['application' => $application, 'embed' => 1]) }}"
                                    data-permit-pdf="{{ route('applicant.applications.permit.pdf', $application) }}">Unduh / cetak SIK</button>
                        @elseif ($application->status->isApplicantEditable())
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('applicant.applications.edit', $application) }}">{{ $application->status === \App\Enums\ApplicationStatus::Revision ? 'Perbaiki & submit ulang' : 'Lanjutkan draf' }}</a>
                        @else
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('applicant.applications.show', $application) }}">Lihat alur</a>
                        @endif
                        @if ($application->status === \App\Enums\ApplicationStatus::Closed)
                            <a class="btn btn-sm btn-primary ms-2" href="{{ route('applicant.applications.create') }}">Buat pengajuan baru</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">Belum ada pengajuan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $applications->links() }}
    @include('partials.permit-modal')
</section>
@endsection
