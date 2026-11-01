@extends('layouts.app')
@section('title', 'Koreksi pengajuan - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Antrean pemeriksaan')

@section('content')
<section class="section-card role-panel">
    <div class="section-heading">
        <div><div class="section-kicker">KOREKSI OLEH ADMIN</div><h2>Perbaiki data pengajuan · {{ $application->number }}</h2><p>Setelah dikoreksi, pengajuan kembali ke Approver tahap {{ $application->current_stage }}.</p></div>
        @include('partials.status-badge', ['status' => $application->status])
    </div>

    <div class="alert alert-warning" role="note">
        @if ($returnEvent)
            Catatan RETURN dari Approver ({{ $returnEvent->actor_name }}): {{ $returnEvent->comment }}
        @else
            Tidak ada catatan RETURN tersimpan untuk pengajuan ini.
        @endif
    </div>

    <form method="post" action="{{ route('admin.correction.update', $application) }}" enctype="multipart/form-data" data-dynamic-form novalidate>
        @csrf
        @method('PUT')
        @include('partials.application-fields', ['application' => $application])

        <label class="detail-field correction-note-field">Catatan koreksi Admin
            <textarea class="form-control" name="correctionNote" rows="2" placeholder="Ringkasan perubahan untuk approver..." required>{{ old('correctionNote') }}</textarea>
        </label>

        <div class="d-flex gap-2 justify-content-end mt-3">
            <a class="btn btn-light" href="{{ route('admin.queues') }}">Batal</a>
            <button class="btn btn-primary" type="submit" data-validate>Kirim kembali ke Approver tahap {{ $application->current_stage }}</button>
        </div>
    </form>
</section>

<section class="section-card role-panel-card">
    <div class="section-heading"><div><div class="section-kicker">SURAT TIDAK VALID</div><h2>Tutup pengajuan</h2><p>Gunakan bila surat permohonan tidak valid; pemohon harus mengajukan ulang dari awal.</p></div></div>
    <form method="post" action="{{ route('admin.correction.close', $application) }}" data-close-form>
        @csrf
        <label class="form-check close-request-check">
            <input class="form-check-input" type="checkbox" name="invalidRequestLetter" value="1" data-close-toggle>
            <span class="form-check-label">Surat permohonan tidak valid; tutup pengajuan ini agar pemohon mengajukan ulang dari awal.</span>
        </label>
        <label class="detail-field mt-2">Alasan penutupan
            <textarea class="form-control" name="correctionNote" rows="2" placeholder="Jelaskan mengapa surat tidak valid..."></textarea>
        </label>
        <button class="btn btn-outline-danger mt-3" type="submit" data-close-button disabled>Tutup pengajuan (CLOSED)</button>
    </form>
</section>
@endsection
