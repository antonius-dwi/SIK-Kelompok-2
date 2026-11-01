@extends('layouts.app')
@section('title', 'Keputusan pemeriksaan - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Antrean persetujuan')

@section('content')
@php $step = $application->approvalSteps->firstWhere('stage', $application->current_stage); @endphp
<section class="section-card role-panel">
    <div class="section-heading">
        <div>
            <div class="section-kicker">{{ $application->number }}</div>
            <h2>Keputusan pemeriksaan</h2>
            <p>Tahap {{ $application->current_stage }} dari {{ $application->total_stages }} · {{ $step?->approver_title ?? 'Penyetuju' }}</p>
        </div>
        @include('partials.status-badge', ['status' => $application->status])
    </div>

    <p class="modal-intro">Tinjau rincian pengajuan {{ $application->workSubject() }}. Return dan Reject wajib disertai catatan.</p>
    @include('partials.application-details', ['application' => $application])

    <form method="post" action="{{ route('approver.decide', $application) }}" data-decision-form>
        @csrf
        <fieldset class="decision-options"><legend class="form-label">Keputusan Anda</legend>
            @php $selected = old('decision', 'approve'); @endphp
            <label class="decision-option"><input type="radio" name="decision" value="approve" @checked($selected === 'approve')><span class="decision-radio"></span><span><strong>Setujui</strong><small>Lanjutkan ke tahap persetujuan berikutnya</small></span><i class="fa-solid fa-circle-check option-approve"></i></label>
            <label class="decision-option"><input type="radio" name="decision" value="return" @checked($selected === 'return')><span class="decision-radio"></span><span><strong>Kembalikan ke admin</strong><small>Kirim ke antrean perbaikan Admin</small></span><i class="fa-solid fa-rotate-left option-return"></i></label>
            <label class="decision-option"><input type="radio" name="decision" value="reject" @checked($selected === 'reject')><span class="decision-radio"></span><span><strong>Tolak pengajuan</strong><small>Akhiri alur persetujuan</small></span><i class="fa-solid fa-circle-xmark option-reject"></i></label>
        </fieldset>

        <label for="decisionComment" class="form-label comment-label">Catatan <span data-comment-requirement>(opsional untuk persetujuan)</span></label>
        <textarea class="form-control @error('comment') is-invalid @enderror" id="decisionComment" name="comment" rows="3" placeholder="Tambahkan catatan untuk pemohon atau admin...">{{ old('comment') }}</textarea>
        <div class="invalid-feedback">{{ $errors->first('comment') ?: 'Catatan wajib diisi untuk pengembalian atau penolakan.' }}</div>
        <div class="form-text">Keputusan dan catatan Anda akan ditambahkan ke riwayat aktivitas.</div>

        <div class="d-flex gap-2 justify-content-end mt-3">
            <a class="btn btn-light" href="{{ route('approver.queue') }}">Batal</a>
            <button class="btn btn-primary" type="submit" data-confirm-button>Konfirmasi persetujuan <i class="fa-solid fa-arrow-right ms-2"></i></button>
        </div>
    </form>
</section>

<section class="section-card role-panel-card">
    <div class="section-heading"><div><div class="section-kicker">RIWAYAT</div><h2>Riwayat approval</h2></div></div>
    @include('partials.history-table', ['histories' => $application->histories])
</section>
@endsection
