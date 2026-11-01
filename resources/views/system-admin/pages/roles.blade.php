@extends('layouts.app')
@section('title', 'Peran - Sistem Izin Kerja Sisi Udara')
@section('crumb_parent', 'Administrasi sistem')
@section('crumb', 'Peran')

@section('content')
@php
    // Label ramah pengguna untuk kolom "Halaman Awal" — nama route internal tidak ditampilkan
    // ke pengguna. Kunci mengikuti Role::homeRoute(); peran baru tetap tampil apa adanya.
    $homeLabels = [
        'applicant.dashboard' => 'Dashboard Pemohon',
        'admin.queues' => 'Antrean Admin',
        'approver.queue' => 'Antrean Persetujuan',
        'management.index' => 'Dashboard Manajemen',
        'system-admin.dashboard' => 'Dashboard Administrasi Sistem',
    ];
@endphp
<div class="system-admin">
    <div class="page-heading role-heading">
        <div>
            <div class="eyebrow">PENGELOLAAN SISTEM</div>
            <h1>Peran</h1>
            <p>{{ $roles->count() }} peran dikenal sistem, dipakai oleh {{ $totalUsers }} akun.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('system-admin.users.index') }}">Kelola pengguna</a>
    </div>

    <article class="section-card role-panel-card">
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>NAMA PERAN</th><th>NILAI TEKNIS</th><th>JUMLAH AKUN</th><th>HALAMAN AWAL</th><th>PENUGASAN AKUN</th></tr></thead>
            <tbody>
            @foreach ($roles as $role)
                <tr>
                    <td>{{ $role['label'] }}</td>
                    <td><span class="role-tech-badge">{{ $role['value'] }}</span></td>
                    <td>{{ $role['count'] }}</td>
                    <td>{{ $homeLabels[$role['home']] ?? $role['home'] }}</td>
                    <td>
                        @forelse ($role['users'] as $akun)
                            <div class="assignment-row">
                                <span class="assignment-name">
                                    {{ $akun->name }}
                                    <small>{{ $akun->username }}@if ($akun->departments->isNotEmpty()) · {{ $akun->departments->pluck('name')->implode(', ') }}@endif</small>
                                </span>
                                @if ($akun->is_active)
                                    <form method="post" action="{{ route('system-admin.roles.revoke', $akun) }}" data-confirm="Cabut penugasan peran {{ $role['label'] }} untuk {{ $akun->name }}? Akun akan dinonaktifkan, tidak dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
                                @else
                                    <span class="badge-soft">nonaktif</span>
                                @endif
                            </div>
                        @empty
                            <span class="text-muted-soft">Belum ada akun.</span>
                        @endforelse
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    </article>
</div>
@endsection
