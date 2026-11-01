@extends('layouts.app')
@section('title', 'Template alur kerja - Sistem Izin Kerja Sisi Udara')
@section('crumb_parent', 'Administrasi sistem')
@section('crumb', 'Template alur kerja')

@section('content')
<div class="system-admin">
    <div class="page-heading role-heading">
        <div>
            <div class="eyebrow">KONFIGURASI WORKFLOW</div>
            <h1>Template alur kerja</h1>
            <p>{{ $activeCount }} templat aktif dari {{ $workflows->count() }} kombinasi area + jenis pekerjaan{{ $inactiveCount ? ', ' . $inactiveCount . ' nonaktif' : '' }}.</p>
        </div>
        <button class="btn btn-primary" type="button" data-workflow-add data-action="{{ route('system-admin.workflows.store') }}">Tambah templat</button>
    </div>

    <article class="section-card role-panel-card">
        <div class="table-responsive role-table-wrap"><table class="table role-table">
            <thead><tr><th>AREA</th><th>JENIS PEKERJAAN</th><th>TAHAP &amp; PENYETUJU</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
            <tbody>
            @forelse ($workflows as $flow)
                @php $stepsJson = $flow->steps->map(fn ($s) => ['title' => $s->approver_title, 'approver' => $s->approver_user_id])->values(); @endphp
                <tr>
                    <td>{{ $flow->area->name }}</td>
                    <td>{{ $flow->workType->name }}</td>
                    <td>{{ $flow->steps->map(fn ($s) => $s->stage . '. ' . $s->approver_title . ' (' . ($s->approver?->name ?? '—') . ')')->implode(' → ') ?: '—' }}</td>
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
        <p class="form-text config-note">Satu kombinasi area + jenis pekerjaan hanya boleh punya satu templat. Pengajuan yang sudah berjalan tetap memakai alur saat diverifikasi.</p>
    </article>
</div>

@push('modals')
@php
    // Form modal mana yang terakhir dikirim (dipakai penanda is-invalid + buka-ulang modal).
    [$errForm, $invalidOn] = \App\Support\FormErrorState::detect($errors, old('_form'));
@endphp
@include('system-admin.partials.modal-workflow')
@endpush

@include('system-admin.partials.modal-reopen')
@endsection
