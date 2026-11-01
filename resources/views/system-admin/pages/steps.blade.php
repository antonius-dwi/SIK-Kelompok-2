@extends('layouts.app')
@section('title', 'Langkah persetujuan - Sistem Izin Kerja Sisi Udara')
@section('crumb_parent', 'Administrasi sistem')
@section('crumb', 'Langkah persetujuan')

@section('content')
<div class="system-admin">
    <div class="page-heading role-heading">
        <div>
            <div class="eyebrow">KONFIGURASI WORKFLOW</div>
            <h1>Langkah persetujuan</h1>
            <p>{{ $totalSteps }} langkah pada {{ $usedTemplates }} templat alur. Halaman ini hanya-baca; ubah langkah melalui templat alurnya.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('system-admin.workflows.index') }}">Kelola templat alur</a>
    </div>

    <article class="section-card role-panel-card">
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>AREA</th><th>JENIS PEKERJAAN</th><th>TAHAP</th><th>JABATAN PENYETUJU</th><th>AKUN APPROVER</th><th>STATUS TEMPLAT</th></tr></thead>
            <tbody>
            @forelse ($steps as $step)
                <tr>
                    <td>{{ $step['flow']->area->name }}</td>
                    <td>{{ $step['flow']->workType->name }}</td>
                    <td>{{ $step['stage'] }}</td>
                    <td>{{ $step['title'] }}</td>
                    <td>{{ $step['approver']?->name ?? '—' }}<span class="table-subtext">{{ $step['approver']?->username }}</span></td>
                    <td>{{ $step['flow']->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">Belum ada langkah persetujuan.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <p class="form-text config-note">Urutan tahap menentukan alur persetujuan: tahap 1 diputuskan lebih dulu, lalu naik ke tahap berikutnya.</p>
    </article>
</div>
@endsection
