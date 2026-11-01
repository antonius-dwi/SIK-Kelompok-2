@extends('layouts.app')
@section('title', 'Verifikasi pengajuan - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Antrean pemeriksaan')

@section('content')
<section class="section-card role-panel">
    <div class="section-heading">
        <div><div class="section-kicker">ANTREAN SUBMITTED</div><h2>Verifikasi pengajuan · {{ $application->number }}</h2><p>Periksa kelengkapan data dan dokumen sebelum diteruskan.</p></div>
        @include('partials.status-badge', ['status' => $application->status])
    </div>
    @include('partials.application-details', ['application' => $application])

    <form method="post" action="{{ route('admin.verification.process', $application) }}" data-verification-form>
        @csrf
        <label for="verificationNote" class="form-label comment-label">Catatan verifikator <span>(wajib diisi bila dikembalikan)</span></label>
        <textarea id="verificationNote" name="verificationNote" class="form-control @error('verificationNote') is-invalid @enderror" rows="3" placeholder="Catatan untuk pemohon...">{{ old('verificationNote') }}</textarea>
        <div class="invalid-feedback">{{ $errors->first('verificationNote') ?: 'Catatan wajib diisi untuk revisi.' }}</div>
        <div class="form-text">Pilih teruskan ke persetujuan atau kembalikan kepada pemohon untuk revisi.</div>

        <div class="d-flex gap-2 justify-content-end mt-3">
            <a class="btn btn-light" href="{{ route('admin.queues') }}">Kembali</a>
            <button class="btn btn-outline-warning" type="submit" name="verificationAction" value="revision">Kembalikan ke pemohon (REVISION)</button>
            <button class="btn btn-primary" type="submit" name="verificationAction" value="forward">Teruskan ke persetujuan</button>
        </div>
    </form>
</section>
@endsection
