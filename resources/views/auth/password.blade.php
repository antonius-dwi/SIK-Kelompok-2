@extends('layouts.guest')
@section('title', 'Lupa kata sandi - Sistem Izin Kerja Sisi Udara')

@section('content')
<div class="login-bg" aria-hidden="true"></div>
<div class="login-card">
    <div class="login-split">
        <div class="login-left">
            <div class="login-left-inner">
                <a class="brand login-brand login-brand-plate" href="{{ route('login') }}">
                    <img src="{{ asset('images/login-banner-injourney.png') }}?v={{ filemtime(public_path('images/login-banner-injourney.png')) }}"
                         alt="Sistem Informasi Surat Izin Kerja — InJourney Airports" class="login-logo">
                </a>

                <p class="section-kicker">BANTUAN AKUN</p>
                <h1>Lupa kata sandi?</h1>
                <p class="login-description">Silakan hubungi SIK Support Bandara Jenderal Ahmad Yani melalui 0-800-123-4567 (bebas pulsa, hari kerja 08.00–17.00 WIB) atau email support@siksystem.co.id untuk verifikasi identitas dan bantuan pengaturan ulang kata sandi.</p>

                <div class="login-help-box">
                    <strong>Siapkan saat menghubungi SIK Support:</strong>
                    <ul>
                        <li>Nama pengguna Anda</li>
                        <li>Nama lengkap Anda</li>
                        <li>Unit / departemen Anda</li>
                    </ul>
                    <p>SIK Support akan memverifikasi identitas Anda sebelum mengatur ulang kata sandi.</p>
                </div>

                <a class="btn btn-primary w-100 fw-semibold" href="{{ route('login') }}">Kembali ke login</a>

                <div class="login-divider"></div>

                <div class="login-footer">
                    Bandar Udara Internasional Jenderal Ahmad Yani Semarang
                </div>
            </div>
        </div>

        <div class="login-right">
            <img src="{{ asset('images/login-staff-fit.png') }}?v={{ filemtime(public_path('images/login-staff-fit.png')) }}" alt="Petugas bandara" class="login-right-img">
        </div>
    </div>
</div>
@endsection
