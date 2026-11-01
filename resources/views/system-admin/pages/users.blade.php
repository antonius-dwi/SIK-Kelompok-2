@extends('layouts.app')
@section('title', 'Pengguna - Sistem Izin Kerja Sisi Udara')
@section('crumb_parent', 'Administrasi sistem')
@section('crumb', 'Pengguna')

@section('content')
<div class="system-admin">
    <div class="page-heading role-heading">
        <div>
            <div class="eyebrow">PENGELOLAAN SISTEM</div>
            <h1>Pengguna</h1>
            @if ($selectedRole)
                <p>Menampilkan {{ $users->count() }} pengguna peran {{ $selectedRole->label() }} dari {{ $totalUsers }} pengguna.</p>
            @else
                <p>{{ $activeCount }} akun aktif dari {{ $totalUsers }} pengguna{{ $inactiveCount ? ', ' . $inactiveCount . ' nonaktif' : '' }}.</p>
            @endif
        </div>
        <div class="d-flex gap-2 align-items-end">
            <form method="get" action="{{ route('system-admin.users.index') }}">
                <label class="filter-field">Peran
                    <select class="form-select form-select-sm" name="role" onchange="this.form.submit()">
                        <option value="">Semua peran ({{ $totalUsers }})</option>
                        @foreach ($roles as $roleOption)
                            <option value="{{ $roleOption->value }}" @selected($roleFilter === $roleOption->value)>{{ $roleOption->label() }} ({{ $roleCounts[$roleOption->value] ?? 0 }})</option>
                        @endforeach
                    </select>
                </label>
            </form>
            @if ($roleFilter !== '')
                <a class="btn btn-sm btn-light" href="{{ route('system-admin.users.index') }}">Reset</a>
            @endif
            <button class="btn btn-primary" type="button" data-user-add data-action="{{ route('system-admin.users.store') }}">Tambah pengguna</button>
        </div>
    </div>

    <article class="section-card role-panel-card">
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>NAMA</th><th>NAMA PENGGUNA</th><th>PERAN</th><th>UNIT / DEPARTEMEN</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
            <tbody>
            @forelse ($users as $user)
                @php $unitNames = $user->departments->pluck('name')->all(); @endphp
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->role->label() }}</td>
                    <td>
                        @forelse ($unitNames as $unit)
                            <span class="unit-chip">{{ $unit }}</span>
                        @empty
                            {{ $user->department?->name ?? '—' }}
                        @endforelse
                    </td>
                    <td>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        <div class="cell-actions">
                            <button class="btn btn-sm btn-outline-primary" type="button" data-user-edit data-action="{{ route('system-admin.users.update', $user) }}" data-name="{{ $user->name }}" data-username="{{ $user->username }}" data-role="{{ $user->role->value }}" data-departments="{{ json_encode($user->assignedDepartmentIds()) }}" data-department="{{ $user->department_id }}">Ubah</button>
                            <form method="post" action="{{ route('system-admin.users.toggle', $user) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                            <form method="post" action="{{ route('system-admin.users.destroy', $user) }}" data-confirm="Hapus pengguna {{ $user->name }}? Akun yang memiliki riwayat SIK tidak dihapus permanen, hanya dinonaktifkan.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">{{ $selectedRole ? 'Belum ada pengguna dengan peran ' . $selectedRole->label() . '.' : 'Belum ada pengguna.' }}</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <p class="form-text config-note">Kata sandi minimal 8 karakter. Saat mengubah pengguna, biarkan kata sandi kosong untuk mempertahankan yang lama. Approver boleh ditugaskan ke lebih dari satu unit.</p>
    </article>
</div>

@push('modals')
@php
    // Form modal mana yang terakhir dikirim (dipakai penanda is-invalid + buka-ulang modal).
    [$errForm, $invalidOn] = \App\Support\FormErrorState::detect($errors, old('_form'));
@endphp
@include('system-admin.partials.modal-user')
@endpush

@include('system-admin.partials.modal-reopen')
@endsection
