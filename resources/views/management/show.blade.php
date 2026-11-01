@extends('layouts.app')
@section('title', 'Detail SIK - Sistem Izin Kerja Sisi Udara')
@section('crumb', 'Pemantauan')

@section('content')
<section class="section-card role-panel">
    <div class="section-heading">
        <div><div class="section-kicker">DETAIL SIK (HANYA BACA)</div><h2>{{ $application->permit_number ?: $application->number }}</h2><p>{{ $application->workSubject() }} · {{ $application->applicant_name }}</p></div>
        <div>@include('partials.status-badge', ['status' => $application->status]) <a class="btn btn-light btn-sm ms-2" href="{{ route('management.index') }}">Kembali</a></div>
    </div>
    @include('partials.application-details', ['application' => $application])
    <h3 class="mt-4">Riwayat approval</h3>
    @include('partials.history-table', ['histories' => $application->histories])
</section>
@endsection
