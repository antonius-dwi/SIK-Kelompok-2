@extends('layouts.app')
@section('title', 'Antrean pemeriksaan - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Antrean pemeriksaan')

@section('content')
<div class="page-heading role-heading"><div><div class="eyebrow">RUANG KERJA ADMIN</div><h1>Antrean pemeriksaan &amp; penerbitan</h1><p>Verifikasi permohonan, tangani koreksi, dan terbitkan SIK.</p></div></div>

<div class="metric-grid role-metrics">
    <article class="metric-card"><div class="metric-icon icon-amber"><i class="fa-regular fa-paper-plane"></i></div><span class="metric-label">Menunggu verifikasi</span><strong>{{ $submitted->count() }}</strong></article>
    <article class="metric-card"><div class="metric-icon icon-violet"><i class="fa-solid fa-rotate-left"></i></div><span class="metric-label">Perlu koreksi</span><strong>{{ $corrections->count() }}</strong></article>
    <article class="metric-card"><div class="metric-icon icon-green"><i class="fa-solid fa-stamp"></i></div><span class="metric-label">Siap diterbitkan</span><strong>{{ $approved->count() }}</strong></article>
</div>

<div class="section-card role-panel-card" id="submitted">
    <div class="section-heading"><div><div class="section-kicker">ANTREAN DIAJUKAN</div><h2>Verifikasi pengajuan baru</h2><p>Periksa kelengkapan data dan dokumen sebelum diteruskan.</p></div><span class="badge status-badge status-submitted"><span></span>Diajukan</span></div>
    <div class="table-responsive role-table-wrap"><table class="table role-table">
        <thead><tr><th>NOMOR SIK</th><th>PEMOHON / PEKERJAAN</th><th>AREA</th><th>DIAJUKAN</th><th>TINDAKAN</th></tr></thead>
        <tbody>
        @forelse ($submitted as $application)
            <tr>
                <td>{{ $application->number }}</td>
                <td><strong>{{ $application->applicant_name }}</strong><span class="table-subtext">{{ $application->workSubject() }}</span></td>
                <td>{{ $application->area?->name }}</td>
                <td>{{ ($application->submitted_at ?? $application->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</td>
                <td>
                    <form method="post" action="{{ route('admin.verification.start', $application) }}">@csrf
                        <button class="btn btn-sm btn-outline-primary" type="submit">{{ $application->status === \App\Enums\ApplicationStatus::Submitted ? 'Verifikasi' : 'Lanjutkan verifikasi' }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Tidak ada pengajuan menunggu verifikasi.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

<div class="section-card role-panel-card" id="correction">
    <div class="section-heading"><div><div class="section-kicker">ANTREAN KOREKSI</div><h2>Koreksi dari penyetuju</h2><p>Perbaiki data sesuai catatan; pengajuan tidak dikembalikan langsung ke pemohon.</p></div><span class="badge status-badge status-correction"><span></span>Koreksi</span></div>
    <div class="table-responsive role-table-wrap"><table class="table role-table">
        <thead><tr><th>NOMOR SIK</th><th>PEMOHON / PEKERJAAN</th><th>CATATAN PENYETUJU</th><th>TINDAKAN</th></tr></thead>
        <tbody>
        @forelse ($corrections as $application)
            <tr>
                <td>{{ $application->number }}</td>
                <td><strong>{{ $application->applicant_name }}</strong><span class="table-subtext">{{ $application->workSubject() }}</span></td>
                <td>{{ $application->correction_note ?: 'Periksa catatan approver dan perbarui data.' }}</td>
                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.correction.edit', $application) }}">Tangani koreksi</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="empty-state">Tidak ada pengajuan perlu koreksi.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

<div class="section-card role-panel-card" id="issuance">
    <div class="section-heading"><div><div class="section-kicker">ANTREAN DISETUJUI</div><h2>Siap diterbitkan</h2><p>Buat nomor SIK dan terbitkan dokumen izin kerja.</p></div><span class="badge status-badge status-approved"><span></span>Disetujui</span></div>
    <div class="table-responsive role-table-wrap"><table class="table role-table">
        <thead><tr><th>NOMOR PENGAJUAN</th><th>PEMOHON / PEKERJAAN</th><th>AREA</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
        <tbody>
        @forelse ($approved as $application)
            <tr>
                <td>{{ $application->number }}</td>
                <td><strong>{{ $application->applicant_name }}</strong><span class="table-subtext">{{ $application->workSubject() }}</span></td>
                <td>{{ $application->area?->name }}</td>
                <td>@include('partials.status-badge', ['status' => $application->status])</td>
                <td><a class="btn btn-sm btn-primary" href="{{ route('admin.issuance.create', $application) }}">Terbitkan SIK</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Tidak ada SIK menunggu penerbitan.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

<div class="section-card role-panel-card" id="history">
    <div class="section-heading">
        <div><div class="section-kicker">AUDIT PENGAJUAN</div><h2>Riwayat &amp; audit per pengajuan</h2><p>Riwayat aktivitas khusus pengajuan yang dipilih.</p></div>
        <form method="get" action="{{ route('admin.queues') }}#history">
            <label class="filter-field">Pengajuan
                <select class="form-select form-select-sm" name="application" onchange="this.form.submit()">
                    <option value="">Pilih pengajuan</option>
                    @foreach ($applicationOptions as $option)
                        <option value="{{ $option->id }}" @selected($selected?->id === $option->id)>{{ $option->number }} · {{ $option->applicant_name }}</option>
                    @endforeach
                </select>
            </label>
        </form>
    </div>
    <h3 class="h6 mt-3">Riwayat approval</h3>
    @include('partials.history-table', ['histories' => $selected?->histories ?? collect()])
    <h3 class="h6 mt-4">Audit pengajuan</h3>
    <div class="table-responsive role-table-wrap"><table class="table role-table">
        <thead><tr><th>WAKTU (WIB)</th><th>PENGGUNA</th><th>AKTIVITAS</th><th>DETAIL</th></tr></thead>
        <tbody>
        @forelse ($selectedAudit as $log)
            <tr><td>{{ $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB</td><td>{{ $log->user_name }}</td><td>{{ $log->action }}</td><td>{{ $log->detail ?: '—' }}</td></tr>
        @empty
            <tr><td colspan="4" class="empty-state">Belum ada audit untuk pengajuan ini.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
