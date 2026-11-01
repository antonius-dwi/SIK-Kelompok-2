@extends('layouts.app')
@section('title', 'Rekap SIK - Sistem Izin Kerja Sisi Udara')
@section('crumb_parent', 'Administrasi sistem')
@section('crumb', 'Rekap SIK')

@section('content')
@php
    $basisTerbit = $filters['basis'] === 'terbit';
    $periode = \Illuminate\Support\Carbon::parse($filters['from'])->translatedFormat('d F Y')
        . ' — ' . \Illuminate\Support\Carbon::parse($filters['to'])->translatedFormat('d F Y');
@endphp
<div class="system-admin">
    <div class="page-heading role-heading">
        <div>
            <div class="eyebrow">ADMINISTRASI SISTEM</div>
            <h1>Rekap SIK</h1>
            <p>{{ $rows->total() }} dokumen pada periode {{ $periode }} (basis: {{ $basisTerbit ? 'tanggal terbit' : 'tanggal diajukan' }}).</p>
        </div>
        <div class="recap-heading-actions">
            <a class="btn btn-outline-primary" href="{{ route('system-admin.recap.index') }}">Reset filter</a>
            <a class="btn btn-primary" href="{{ route('system-admin.recap.zip', request()->only(['from', 'to', 'basis'])) }}">Unduh ZIP periode ini</a>
        </div>
    </div>

    <article class="section-card role-panel-card recap-filter-card">
        <form method="get" action="{{ route('system-admin.recap.index') }}" class="recap-filters">
            <label class="detail-field">Dari tanggal
                <input class="form-control" type="date" name="from" value="{{ $filters['from'] }}">
            </label>
            <label class="detail-field">Sampai tanggal
                <input class="form-control" type="date" name="to" value="{{ $filters['to'] }}">
            </label>
            <label class="detail-field">Basis tanggal
                <select class="form-select" name="basis">
                    <option value="terbit" @selected($basisTerbit)>Tanggal terbit (SIK sah)</option>
                    <option value="diajukan" @selected(! $basisTerbit)>Tanggal diajukan</option>
                </select>
            </label>
            <div class="recap-filter-actions">
                <button class="btn btn-primary" type="submit">Terapkan</button>
            </div>
        </form>
    </article>

    <article class="section-card role-panel-card">
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>NOMOR SIK</th><th>PEMOHON</th><th>AREA</th><th>JENIS PEKERJAAN</th><th>DIKIRIM</th><th>TERBIT</th><th>AKSI</th></tr></thead>
            <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->permit_number ?: $row->number }}</td>
                    <td>{{ $row->applicant_name }}</td>
                    <td>{{ $row->area?->name ?? '—' }}</td>
                    <td>{{ $row->workType?->name ?? '—' }}</td>
                    <td>{{ $row->submitted_at?->translatedFormat('d M Y') ?? '—' }}</td>
                    <td>{{ $row->issue_date?->translatedFormat('d M Y') ?? 'Belum terbit' }}</td>
                    <td>
                        @if ($row->status === \App\Enums\ApplicationStatus::Issued && $row->permit_number)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('system-admin.recap.pdf', $row) }}">Unduh PDF</a>
                        @else
                            <span class="badge-soft">{{ $row->status->label() }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">Tidak ada SIK pada periode ini. Ubah rentang tanggal atau basis tanggal.</td></tr>
            @endforelse
            </tbody>
        </table></div>

        @if ($rows->hasPages())
            <div class="recap-pagination">{{ $rows->links() }}</div>
        @endif

        <p class="form-text config-note">
            Total SIK terbit pada rentang ini: <strong>{{ $totalTerbit }}</strong> dokumen ·
            total pengajuan yang dikirim: <strong>{{ $totalDiajukan }}</strong>.
            Unduhan massal memuat hanya dokumen yang benar-benar terbit.
        </p>
    </article>
</div>
@endsection
