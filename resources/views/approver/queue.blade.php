@extends('layouts.app')
@section('title', 'Antrean persetujuan - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Antrean persetujuan')

@section('content')
<div class="page-heading role-heading"><div><div class="eyebrow">RUANG KERJA APPROVER</div><h1>Antrean persetujuan</h1><p>Pengajuan ditampilkan sesuai tahap persetujuan Anda.</p></div></div>
<div class="section-card role-panel-card">
    <div class="section-heading"><div><div class="section-kicker">MENUNGGU TINDAKAN ANDA</div><h2>Pengajuan dalam proses</h2><p>Periksa detail pekerjaan, personel, peralatan, dan dokumen.</p></div><span class="badge status-badge status-approval"><span></span>Dalam persetujuan</span></div>
    <div class="table-responsive role-table-wrap"><table class="table role-table">
        <thead><tr><th>NOMOR PENGAJUAN</th><th>PEMOHON / PEKERJAAN</th><th>AREA</th><th>TAHAP PERSETUJUAN</th><th>TINDAKAN</th></tr></thead>
        <tbody>
        @forelse ($applications as $application)
            <tr>
                <td>{{ $application->number }}</td>
                <td><strong>{{ $application->applicant_name }}</strong><span class="table-subtext">{{ $application->workSubject() }}</span></td>
                <td>{{ $application->area?->name }}</td>
                <td>Tahap {{ $application->current_stage }} dari {{ $application->total_stages }} · {{ $application->approvalSteps->firstWhere('stage', $application->current_stage)?->approver_title }}</td>
                <td><a class="btn btn-sm btn-primary" href="{{ route('approver.show', $application) }}">Tinjau &amp; putuskan</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Tidak ada pengajuan menunggu persetujuan Anda.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
