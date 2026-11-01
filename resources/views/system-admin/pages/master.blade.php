@extends('layouts.app')
@section('title', $title . ' - Sistem Izin Kerja Sisi Udara')
@section('crumb_parent', 'Administrasi sistem')
@section('crumb', $title)

@section('content')
<div class="system-admin">
    <div class="page-heading role-heading">
        <div>
            <div class="eyebrow">MASTER DATA</div>
            <h1>{{ $title }}</h1>
            <p>{{ $activeCount }} aktif dari {{ $rows->count() }} data{{ $inactiveCount ? ', ' . $inactiveCount . ' nonaktif' : '' }}.</p>
        </div>
        <button class="btn btn-primary" type="button" data-master-add data-title="Tambah {{ \Illuminate\Support\Str::lower($title) }}" data-action="{{ route('system-admin.' . $routeSlug . '.store') }}">Tambah {{ $noun }}</button>
    </div>

    <article class="section-card role-panel-card">
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>KODE</th><th>{{ $nameHead }}</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
            <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->code }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        <div class="cell-actions">
                            <button class="btn btn-sm btn-outline-primary" type="button" data-master-edit data-title="Ubah {{ \Illuminate\Support\Str::lower($title) }}" data-action="{{ route('system-admin.' . $routeSlug . '.update', $row->id) }}" data-name="{{ $row->name }}" data-code="{{ $row->code }}">Ubah</button>
                            <form method="post" action="{{ route('system-admin.' . $routeSlug . '.toggle', $row->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" type="submit">{{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                            <form method="post" action="{{ route('system-admin.' . $routeSlug . '.destroy', $row->id) }}" data-confirm="Hapus {{ $noun }} {{ $row->name }}? Data yang masih dipakai pengajuan atau template alur akan ditolak.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty-state">Belum ada data {{ $noun }}.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <p class="form-text config-note">Kode maksimal 10 karakter dan unik. Data tidak dihapus; gunakan Aktifkan/Nonaktifkan agar riwayat pengajuan lama tetap utuh.</p>
    </article>
</div>

@push('modals')
@php
    // Form modal mana yang terakhir dikirim (dipakai penanda is-invalid + buka-ulang modal).
    [$errForm, $invalidOn] = \App\Support\FormErrorState::detect($errors, old('_form'));
@endphp
@include('system-admin.partials.modal-master')
@endpush

@include('system-admin.partials.modal-reopen')
@endsection
