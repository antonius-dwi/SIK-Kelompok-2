@extends('layouts.app')
@section('title', 'Master data & konfigurasi - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Administrasi sistem')

@section('content')
@php
    // Tabel master sederhana (kode + nama + status): Area, Jenis pekerjaan, Departemen.
    $masters = [
        ['slug' => 'areas', 'title' => 'Area kerja', 'noun' => 'area', 'rows' => $areas, 'nameHead' => 'NAMA AREA'],
        ['slug' => 'work-types', 'title' => 'Jenis pekerjaan', 'noun' => 'jenis', 'rows' => $workTypes, 'nameHead' => 'JENIS PEKERJAAN'],
    ];
    $activeAreas = $areas->where('is_active', true);
    $activeTypes = $workTypes->where('is_active', true);
    $activeDepartments = $departments->where('is_active', true);
@endphp

{{-- Lingkup CSS khusus dashboard System Administrator: mencegah aturan density
     di sik.css ikut mengubah dashboard peran lain yang memakai .role-table. --}}
<div class="system-admin">
<div class="page-heading role-heading"><div><div class="eyebrow">ADMINISTRASI SISTEM</div><h1>Master data &amp; konfigurasi</h1><p>Kelola referensi area, pekerjaan, alur persetujuan, dan audit sistem.</p></div></div>

<div class="system-admin-grid">
    @foreach ($masters as $m)
        <article class="section-card role-panel-card" id="{{ $m['slug'] }}">
            <div class="section-heading"><div><div class="section-kicker">MASTER DATA</div><h2>{{ $m['title'] }}</h2></div>
                <button class="btn btn-sm btn-outline-primary" type="button" data-master-add data-title="Tambah {{ $m['title'] }}" data-action="{{ route('system-admin.' . $m['slug'] . '.store') }}">Tambah {{ $m['noun'] }}</button></div>
            <div class="table-responsive role-table-wrap"><table class="table role-table">
                <thead><tr><th>KODE</th><th>{{ $m['nameHead'] }}</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
                <tbody>
                @foreach ($m['rows'] as $row)
                    <tr>
                        <td>{{ $row->code }}</td><td>{{ $row->name }}</td><td>{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td>
                            <div class="cell-actions">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-master-edit data-title="Ubah {{ $m['title'] }}" data-action="{{ route('system-admin.' . $m['slug'] . '.update', $row->id) }}" data-name="{{ $row->name }}" data-code="{{ $row->code }}">Ubah</button>
                                <form method="post" action="{{ route('system-admin.' . $m['slug'] . '.toggle', $row->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" type="submit">{{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </article>
    @endforeach
</div>

<div class="section-card role-panel-card" id="workflows">
    <div class="section-heading"><div><div class="section-kicker">KONFIGURASI PERSETUJUAN</div><h2>Templat alur kerja</h2><p>Pemetaan approver berdasarkan kombinasi area dan jenis pekerjaan.</p></div>
        <button class="btn btn-sm btn-outline-primary" type="button" data-workflow-add data-action="{{ route('system-admin.workflows.store') }}">Tambah templat</button></div>
    <div class="table-responsive role-table-wrap"><table class="table role-table">
        <thead><tr><th>AREA</th><th>JENIS PEKERJAAN</th><th>TAHAP &amp; PENYETUJU</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
        <tbody>
        @forelse ($workflows as $flow)
            @php $stepsJson = $flow->steps->map(fn ($s) => ['title' => $s->approver_title, 'approver' => $s->approver_user_id])->values(); @endphp
            <tr>
                <td>{{ $flow->area->name }}</td><td>{{ $flow->workType->name }}</td>
                <td>{{ $flow->steps->map(fn ($s) => $s->stage . '. ' . $s->approver_title . ' (' . $s->approver->name . ')')->implode(' → ') }}</td>
                <td>{{ $flow->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td>
                    <div class="cell-actions">
                        <button class="btn btn-sm btn-outline-primary" type="button" data-workflow-edit data-action="{{ route('system-admin.workflows.update', $flow) }}" data-area="{{ $flow->area_id }}" data-work-type="{{ $flow->work_type_id }}" data-steps='@json($stepsJson)'>Ubah</button>
                        <form method="post" action="{{ route('system-admin.workflows.toggle', $flow) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" type="submit">{{ $flow->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Belum ada templat alur.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>

<div class="system-admin-grid">
    <article class="section-card role-panel-card span-full" id="users">
        <div class="section-heading"><div><div class="section-kicker">AKSES SISTEM</div><h2>Kelola pengguna</h2></div>
            <button class="btn btn-sm btn-outline-primary" type="button" data-user-add data-action="{{ route('system-admin.users.store') }}">Tambah pengguna</button></div>
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>NAMA</th><th>NAMA PENGGUNA</th><th>PERAN</th><th>DEPARTEMEN</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td><td>{{ $user->username }}</td><td>{{ $user->role->label() }}</td><td>{{ $user->department?->name }}</td><td>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        <div class="cell-actions">
                            <button class="btn btn-sm btn-outline-primary" type="button" data-user-edit data-action="{{ route('system-admin.users.update', $user) }}" data-name="{{ $user->name }}" data-username="{{ $user->username }}" data-role="{{ $user->role->value }}" data-department="{{ $user->department_id }}">Ubah</button>
                            <form method="post" action="{{ route('system-admin.users.toggle', $user) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" type="submit">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    </article>

    <article class="section-card role-panel-card" id="roles">
        <div class="section-heading"><div><div class="section-kicker">AKSES SISTEM</div><h2>Peran</h2></div></div>
        <p class="form-text">Peran bersifat tetap di kode (enum <code>Role</code>); hak akses dipetakan di route dan policy.</p>
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>NAMA PERAN</th></tr></thead>
            <tbody>@foreach ($roles as $role)<tr><td>{{ $role->label() }}</td></tr>@endforeach</tbody>
        </table></div>
    </article>

    <article class="section-card role-panel-card" id="departments">
        <div class="section-heading"><div><div class="section-kicker">ORGANISASI</div><h2>Departemen</h2></div>
            <button class="btn btn-sm btn-outline-primary" type="button" data-master-add data-title="Tambah departemen" data-action="{{ route('system-admin.departments.store') }}">Tambah departemen</button></div>
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>KODE</th><th>NAMA DEPARTEMEN</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
            <tbody>
            @foreach ($departments as $row)
                <tr>
                    <td>{{ $row->code }}</td><td>{{ $row->name }}</td><td>{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        <div class="cell-actions">
                            <button class="btn btn-sm btn-outline-primary" type="button" data-master-edit data-title="Ubah departemen" data-action="{{ route('system-admin.departments.update', $row->id) }}" data-name="{{ $row->name }}" data-code="{{ $row->code }}">Ubah</button>
                            <form method="post" action="{{ route('system-admin.departments.toggle', $row->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" type="submit">{{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    </article>
</div>

<div class="section-card role-panel-card" id="history">
    <div class="section-heading">
        <div><div class="section-kicker">RIWAYAT PENGAJUAN</div><h2>Riwayat approval per pengajuan</h2></div>
        <form method="get" action="{{ route('system-admin.index') }}#history">
            <label class="filter-field">Pengajuan
                <select class="form-select form-select-sm" name="application" onchange="this.form.submit()">
                    <option value="">Pilih pengajuan</option>
                    @foreach ($applicationOptions as $option)<option value="{{ $option->id }}" @selected($selected?->id === $option->id)>{{ $option->number }} · {{ $option->applicant_name }}</option>@endforeach
                </select>
            </label>
        </form>
    </div>
    @include('partials.history-table', ['histories' => $selected?->histories ?? collect()])
</div>

<div class="section-card role-panel-card" id="audit">
    <div class="section-heading"><div><div class="section-kicker">JEJAK AUDIT</div><h2>Log aktivitas sistem</h2><p>Riwayat perubahan dan tindakan pengguna pada sistem (200 terbaru).</p></div></div>
    <div class="table-responsive role-table-wrap"><table class="table role-table">
        <thead><tr><th>WAKTU (WIB)</th><th>PENGGUNA</th><th>AKTIVITAS</th><th>OBJEK</th><th>DETAIL</th></tr></thead>
        <tbody>
        @forelse ($auditLogs as $log)
            <tr><td>{{ $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB</td><td>{{ $log->user_name }}</td><td>{{ $log->action }}</td><td>{{ $log->target ?: '—' }}</td><td>{{ $log->detail ?: '—' }}</td></tr>
        @empty
            <tr><td colspan="5" class="empty-state">Belum ada aktivitas.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
</div>

@push('modals')
@php
    // Form modal mana yang terakhir dikirim (dipakai penanda is-invalid + buka-ulang modal).
    [$errForm, $invalidOn] = \App\Support\FormErrorState::detect($errors, old('_form'));
@endphp
@include('system-admin.partials.modal-master')
@include('system-admin.partials.modal-user')
@include('system-admin.partials.modal-workflow')
@endpush

@include('system-admin.partials.modal-reopen')

@endsection
