@extends('layouts.app')
@section('title', $application->number . ' - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Pengajuan saya')

@section('content')
<section class="section-card role-panel">
    <div class="section-heading">
        <div><div class="section-kicker">DETAIL PENGAJUAN</div><h2>{{ $application->number }}</h2><p>{{ $application->workSubject() }} · {{ $application->applicant_name }}</p></div>
        <div>
            <a class="btn btn-light btn-sm" href="{{ route('applicant.dashboard') }}">Kembali</a>
            @if ($application->status === \App\Enums\ApplicationStatus::Issued)
                <button class="btn btn-sm btn-success" type="button" data-bs-toggle="modal" data-bs-target="#permitModal"
                        data-permit-src="{{ route('applicant.applications.permit', ['application' => $application, 'embed' => 1]) }}"
                        data-permit-pdf="{{ route('applicant.applications.permit.pdf', $application) }}">Unduh / cetak SIK</button>
            @endif
        </div>
    </div>
    @include('partials.application-details', ['application' => $application])
    @include('partials.timeline', ['application' => $application])
    @include('partials.permit-modal')
</section>
@endsection
