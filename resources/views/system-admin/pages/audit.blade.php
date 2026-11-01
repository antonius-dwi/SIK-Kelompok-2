@extends('layouts.app')
@section('title', 'Log aktivitas - Sistem Izin Kerja Sisi Udara')
@section('crumb_parent', 'Administrasi sistem')
@section('crumb', 'Log aktivitas')

@section('content')
<div class="system-admin">
    <div class="page-heading role-heading">
        <div>
            <div class="eyebrow">AUDIT</div>
            <h1>Log aktivitas</h1>
            <p>Menampilkan {{ $logs->count() }} aktivitas terbaru{{ $filter !== '' ? ' untuk filter “' . $filter . '”' : '' }} dari total {{ $total }} catatan.</p>
        </div>
        <form method="get" action="{{ route('system-admin.audit.index') }}" class="d-flex gap-2 align-items-end">
            <label class="filter-field">Aktivitas
                <select class="form-select form-select-sm" name="action" onchange="this.form.submit()">
                    <option value="">Semua aktivitas</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected($filter === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </label>
            @if ($filter !== '')
                <a class="btn btn-sm btn-light" href="{{ route('system-admin.audit.index') }}">Reset</a>
            @endif
        </form>
    </div>

    <article class="section-card role-panel-card">
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>WAKTU (WIB)</th><th>PENGGUNA</th><th>AKTIVITAS</th><th>OBJEK</th><th>DETAIL</th></tr></thead>
            <tbody>
            @forelse ($logs as $log)
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
        </table></div>
        <p class="form-text config-note">Log hanya-baca dan tidak dapat diubah dari antarmuka. Pencatatan dilakukan otomatis oleh <code>AuditLogger</code> pada setiap tindakan penting.</p>
    </article>
</div>
@endsection
