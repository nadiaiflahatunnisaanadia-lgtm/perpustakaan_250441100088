@extends('layouts.app')

@section('title', 'Masuk — Lentera Aksara')

@section('content')
    <div class="container auth-wrap">
        <div class="auth-card">
            <h1>Masuk 250441100088</h1>
            <p>Masuk dengan akunmu untuk melihat koleksi dan mengajukan peminjaman.</p>

            @include('layouts.partials.flash')

            <form method="POST" action="{{ route('login') }}" class="form-stack">
                @csrf

                <label class="field">
                    Email
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Kata sandi
                    <input type="password" name="password" required autocomplete="current-password">
                    @error('password') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="cek">
                    <input type="checkbox" name="remember" value="1"> Ingat saya
                </label>

                <button type="submit" class="btn">Masuk</button>
            </form>

            <p class="auth-alt">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai siswa</a></p>
        </div>
    </div>
@endsection
