@extends('layouts.app')
@section('title', 'SIK ' . $application->permit_number)
@section('crumb', 'Cetak SIK')

@section('content')
<section class="section-card role-panel">
    <div class="section-heading">
        <div><div class="section-kicker">SURAT IZIN KERJA</div><h2>{{ $application->permit_number }}</h2></div>
        <button class="btn btn-primary" type="button" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Cetak / unduh PDF</button>
    </div>
    @include('partials.permit-sheet', ['application' => $application])
</section>
@endsection
