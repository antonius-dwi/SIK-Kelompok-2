@extends('layouts.app')
@section('title', 'Pengajuan izin kerja - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Pengajuan baru')

@section('content')
@php $editing = $application !== null; @endphp
<section class="section-card application-card" id="submission">
    <div class="section-heading">
        <div>
            <div class="section-kicker">FORMULIR PENGAJUAN</div>
            <h2>Pengajuan izin kerja</h2>
            <p>Lengkapi informasi pekerjaan, personel, peralatan, dan dokumen pendukung.</p>
        </div>
        @include('partials.status-badge', ['status' => $application?->status ?? \App\Enums\ApplicationStatus::Draft])
    </div>

    @if ($application?->status === \App\Enums\ApplicationStatus::Revision && $application->correction_note)
        <div class="alert alert-warning py-2" role="note"><strong>Catatan verifikator:</strong> {{ $application->correction_note }}</div>
    @endif

    <nav class="stepper" aria-label="Navigasi bagian formulir">
        <a class="step current" href="#generalDetails" data-form-step="generalDetails"><span>1</span><small>Data umum</small></a><div class="step-line"></div>
        <a class="step" href="#personnel" data-form-step="personnel"><span>2</span><small>Personel</small></a><div class="step-line"></div>
        <a class="step" href="#equipment" data-form-step="equipment"><span>3</span><small>Peralatan</small></a><div class="step-line"></div>
        <a class="step" href="#supportingDocuments" data-form-step="supportingDocuments"><span>4</span><small>Dokumen</small></a>
    </nav>

    <form id="equipmentForm" method="post" enctype="multipart/form-data" data-dynamic-form
          action="{{ $editing ? route('applicant.applications.update', $application) : route('applicant.applications.store') }}" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-context">
            <div><span class="context-label">NOMOR PENGAJUAN</span><strong>{{ $application?->number ?? 'Nomor akan dibuat saat draf disimpan' }}</strong></div>
            <div><span class="context-label">AREA KERJA</span><strong>{{ $application?->area?->name ?? '—' }}</strong></div>
            <div><span class="context-label">JENIS PEKERJAAN</span><strong>{{ $application?->workType?->name ?? '—' }}</strong></div>
            <div><span class="context-label">PERIODE KERJA</span><strong>{{ $application?->start_date ? $application->start_date->translatedFormat('d M Y') . ' – ' . $application->end_date?->translatedFormat('d M Y') : '—' }}</strong></div>
        </div>

        @include('partials.application-fields', ['application' => $application])

        <div class="form-actions">
            <span class="save-note"><i class="fa-regular fa-circle-check"></i> Draf dapat dilanjutkan kapan saja</span>
            <div>
                <button class="btn btn-light me-2" type="submit" name="action" value="draft" formnovalidate>Simpan draf</button>
                <button class="btn btn-primary" type="submit" name="action" value="submit" data-validate>Kirim pengajuan <i class="fa-solid fa-arrow-right ms-2"></i></button>
            </div>
        </div>
    </form>
</section>
@endsection
