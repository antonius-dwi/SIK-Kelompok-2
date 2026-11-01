@extends('layouts.guest')
@section('title', 'Masuk - Sistem Izin Kerja Sisi Udara')

@section('content')
<div class="login-bg" aria-hidden="true"></div>
<div class="login-card">
    <div class="login-split">
            <div class="login-left">
                <div class="login-left-inner">
                    {{-- Banner InJourney Airports + SIK untuk halaman login (logo SIK di sidebar/dashboard tidak berubah). --}}
                    <a class="brand login-brand login-brand-plate" href="{{ route('login') }}">
                        <img src="{{ asset('images/login-banner-injourney.png') }}?v={{ filemtime(public_path('images/login-banner-injourney.png')) }}"
                             alt="Sistem Informasi Surat Izin Kerja — InJourney Airports" class="login-logo">
                    </a>

                    <p class="section-kicker">PORTAL OPERASIONAL</p>
                    <h1>Masuk ke akun Anda</h1>
                    <p class="login-description">Gunakan akun yang diberikan System Administrator untuk membuka ruang kerja sesuai peran.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="post" action="{{ route('login.store') }}">
                        @csrf
                        <label class="detail-field mb-3" for="username">Nama pengguna
                            <input class="form-control" id="username" name="username" value="{{ old('username') }}" autocomplete="username" autofocus required>
                        </label>
                        <label class="detail-field mb-3" for="password">Kata sandi
                            <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                        </label>
                        <button class="btn btn-primary w-100 fw-semibold" type="submit">Masuk</button>
                    </form>

                    <div class="login-secondary">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Ingat Saya</label>
                        </div>
                        <a class="password-link" href="{{ route('password.help') }}">Lupa kata sandi?</a>
                    </div>

                    <div class="login-divider"></div>

                    <div class="login-footer">
                        Bandar Udara Internasional Jenderal Ahmad Yani Semarang
                    </div>
                </div>
            </div>

            <div class="login-right">
                {{-- Foto petugas bandara, disesuaikan ke rasio panel kanan (~0,78) dari "image login.png" dengan latar blur, agar kedua petugas muat penuh tanpa terpotong kiri/kanan. --}}
                <img src="{{ asset('images/login-staff-fit.png') }}?v={{ filemtime(public_path('images/login-staff-fit.png')) }}" alt="Petugas bandara" class="login-right-img">
            </div>
        </div>
    </div>
@endsection
