@extends('layouts.app')
@section('title', 'Terbitkan SIK - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Antrean pemeriksaan')

@section('content')
@php
    /* Nomor & tanggal isian dipakai sebagai data pratinjau; baru tersimpan saat SIK diterbitkan. */
    $draftNumber = old('permitNumber', $suggestedNumber);
    $draftDate = old('issueDate', $today);
    $draftQuery = '?number=' . urlencode($draftNumber) . '&date=' . urlencode($draftDate);
    $previewUrl = route('admin.permit', $application) . '?embed=1&number=' . urlencode($draftNumber) . '&date=' . urlencode($draftDate);
    $pdfUrl = route('admin.permit.pdf', $application) . $draftQuery;
@endphp
<section class="section-card role-panel">
    <div class="section-heading">
        <div><div class="section-kicker">PENERBITAN SURAT IZIN KERJA</div><h2>Terbitkan SIK · {{ $application->number }}</h2><p>Nomor SIK harus unik dan berformat SIK/AYANI/TAHUN/NNN.</p></div>
        @include('partials.status-badge', ['status' => $application->status])
    </div>

    <form method="post" action="{{ route('admin.issuance.store', $application) }}" data-issuance-form>
        @csrf
        <div class="general-details-grid">
            <label class="detail-field">Nomor SIK
                <input class="form-control @error('permitNumber') is-invalid @enderror" id="issuedPermitNumber" name="permitNumber" value="{{ old('permitNumber', $suggestedNumber) }}"
                       placeholder="SIK/AYANI/2026/001" pattern="SIK/AYANI/[0-9]{4}/[0-9]{3}" title="Format nomor SIK: SIK/AYANI/TAHUN/NNN, contoh SIK/AYANI/2026/001" maxlength="18" required>
            </label>
            <label class="detail-field">Tanggal terbit
                <input class="form-control @error('issueDate') is-invalid @enderror" id="issueDate" name="issueDate" type="date" value="{{ old('issueDate', $today) }}" required>
            </label>
        </div>

        <div class="issuance-actions">
            <p class="issuance-note">
                Pratinjau dan unduhan memakai lembar SIK yang sama dengan halaman pemohon (format A4 terbaru);
                nomor dan tanggal mengikuti isian di atas dan baru tersimpan setelah SIK diterbitkan.
            </p>
            <div class="issuance-actions-buttons">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#permitModal"
                        data-permit-src="{{ $previewUrl }}" data-permit-pdf="{{ $pdfUrl }}">
                    <i class="fa-solid fa-eye me-2"></i>Pratinjau SIK
                </button>
                <a class="btn btn-outline-secondary" href="{{ $pdfUrl }}" data-issued-pdf>
                    <i class="fa-solid fa-download me-2"></i>Unduh PDF
                </a>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-3">
            <a class="btn btn-light" href="{{ route('admin.queues') }}">Batal</a>
            <button class="btn btn-primary" type="submit">Terbitkan SIK</button>
        </div>
    </form>
</section>

@include('partials.permit-modal')
@endsection
