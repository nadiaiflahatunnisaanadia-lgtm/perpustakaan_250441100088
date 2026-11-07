@extends('layouts.app')

@section('title', 'Daftar — Lentera Aksara')

@section('content')
    <div class="container auth-wrap">
        <div class="auth-card">
            <h1>Daftar</h1>
            <p>Buat akun siswa untuk meminjam buku. Akun staf (petugas/admin) dibuat oleh admin.</p>

            <form method="POST" action="{{ route('register') }}" class="form-stack">
                @csrf

                <label class="field">
                    Nama lengkap
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                    @error('name') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Email
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                    @error('email') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Nomor telepon
                    <input type="text" name="no_telepon" value="{{ old('no_telepon') }}" required autocomplete="tel">
                    @error('no_telepon') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Kata sandi
                    <input type="password" name="password" required autocomplete="new-password">
                    @error('password') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Konfirmasi kata sandi
                    <input type="password" name="password_confirmation" required autocomplete="new-password">
                </label>

                <button type="submit" class="btn">Daftar</button>
            </form>

            <p class="auth-alt">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
        </div>
    </div>
@endsection
