@extends('layouts.app')
@section('title', 'Pengajuan izin kerja - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Pengajuan baru')

@section('content')
@php
    $editing = $application !== null;
    // Hitung error per bagian form, untuk penanda stepper dan tautan lompat.
    // Kunci error array tercatat sebagai "field.0", "field.1", atau kunci induknya.
    $errKeys = isset($errors) ? collect($errors->keys()) : collect();
    $countSection = function (array $exact, array $prefixes = []) use ($errKeys) {
        return $errKeys->filter(function ($key) use ($exact, $prefixes) {
            if (in_array($key, $exact, true)) return true;
            foreach ($prefixes as $prefix) {
                if ($key === $prefix || str_starts_with($key, $prefix . '.')) return true;
            }
            return false;
        })->count();
    };
    $errGeneral = $countSection(['applicantName', 'contactName', 'jobTitle', 'workDescription', 'area', 'workType', 'startDate', 'endDate']);
    $errPersonnel = $countSection([], ['personel_nama', 'personel_hp', 'personel_plat']);
    $errEquipment = $countSection([], ['barang_nama', 'barang_qty', 'barang_brand', 'barang_model', 'barang_sn', 'barang_asset_no', 'barang_kondisi', 'barang_pergerakan', 'barang_frekuensi', 'barang_consumable', 'barang_foto', 'barang_id']);
    $errDocuments = $countSection(['requestLetter', 'attachments'], ['attachments']);
    $errTotal = $errGeneral + $errPersonnel + $errEquipment + $errDocuments;
@endphp
<section class="section-card application-card" id="submission">
    <div class="section-heading">
        <div>
            <div class="section-kicker">FORMULIR PENGAJUAN</div>
            <h2>Pengajuan izin kerja</h2>
            <p>Lengkapi informasi pekerjaan, personel, peralatan, dan dokumen pendukung. Tanda <span class="text-danger">*</span> wajib diisi saat <strong>Kirim pengajuan</strong>; saat <strong>Simpan draf</strong> boleh dikosongkan.</p>
        </div>
        @include('partials.status-badge', ['status' => $application?->status ?? \App\Enums\ApplicationStatus::Draft])
    </div>

    @if ($application?->status === \App\Enums\ApplicationStatus::Revision && $application->correction_note)
        <div class="alert alert-warning py-2" role="note"><strong>Catatan verifikator:</strong> {{ $application->correction_note }}</div>
    @endif

    @if ($errTotal > 0)
        <div class="alert alert-danger py-2 form-error-jump" role="alert">
            <strong>Belum lengkap ({{ $errTotal }}):</strong>
            @if ($errGeneral > 0)<a href="#generalDetails">Data umum ({{ $errGeneral }})</a>@endif
            @if ($errPersonnel > 0)@if ($errGeneral > 0) · @endif<a href="#personnel">Personel ({{ $errPersonnel }})</a>@endif
            @if ($errEquipment > 0)@if ($errGeneral > 0 || $errPersonnel > 0) · @endif<a href="#equipment">Peralatan ({{ $errEquipment }})</a>@endif
            @if ($errDocuments > 0)@if ($errGeneral > 0 || $errPersonnel > 0 || $errEquipment > 0) · @endif<a href="#supportingDocuments">Dokumen ({{ $errDocuments }})</a>@endif
        </div>
    @endif

    <nav class="stepper" aria-label="Navigasi bagian formulir">
        <a class="step current @if ($errGeneral > 0) has-error @endif" href="#generalDetails" data-form-step="generalDetails"><span>1</span><small>Data umum</small></a><div class="step-line"></div>
        <a class="step @if ($errPersonnel > 0) has-error @endif" href="#personnel" data-form-step="personnel"><span>2</span><small>Personel</small></a><div class="step-line"></div>
        <a class="step @if ($errEquipment > 0) has-error @endif" href="#equipment" data-form-step="equipment"><span>3</span><small>Peralatan</small></a><div class="step-line"></div>
        <a class="step @if ($errDocuments > 0) has-error @endif" href="#supportingDocuments" data-form-step="supportingDocuments"><span>4</span><small>Dokumen</small></a>
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

        @include('partials.application-fields', ['application' => $application, 'showTemplateWarning' => true, 'templateCombos' => $templateCombos ?? []])

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
