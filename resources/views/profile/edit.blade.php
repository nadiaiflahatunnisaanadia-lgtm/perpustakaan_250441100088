@extends('layouts.app')

@section('title', 'Profil — Lentera Aksara')

@section('content')
    <div class="container page">
        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Akun</span>
                <h1>Profil saya</h1>
                <p>Kamu hanya bisa melihat dan mengubah data profil milikmu sendiri.</p>
            </div>
        </div>

        @include('layouts.partials.flash')

        <section class="panel">
            <h2>Data profil</h2>

            <form method="POST" action="{{ route('profile.update') }}" class="form-stack">
                @csrf
                @method('PATCH')

                <label class="field">
                    Nama
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Email
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Nomor telepon
                    <input type="text" name="no_telepon" value="{{ old('no_telepon', $anggota?->no_telepon) }}">
                    @error('no_telepon') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Role
                    <input type="text" value="{{ $user->roles->pluck('name')->implode(', ') }}" disabled>
                    <small>Role hanya bisa diubah oleh admin.</small>
                </label>

                @can('ubah profil sendiri')
                    <div><button type="submit" class="btn">Simpan profil</button></div>
                @endcan
            </form>
        </section>

        @can('ubah profil sendiri')
            <section class="panel">
                <h2>Ganti kata sandi</h2>

                <form method="POST" action="{{ route('password.update') }}" class="form-stack">
                    @csrf
                    @method('PUT')

                    <label class="field">
                        Kata sandi saat ini
                        <input type="password" name="current_password" autocomplete="current-password" required>
                        @error('current_password', 'updatePassword') <span class="galat">{{ $message }}</span> @enderror
                    </label>

                    <label class="field">
                        Kata sandi baru
                        <input type="password" name="password" autocomplete="new-password" required>
                        @error('password', 'updatePassword') <span class="galat">{{ $message }}</span> @enderror
                    </label>

                    <label class="field">
                        Konfirmasi kata sandi baru
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>

                    <div><button type="submit" class="btn">Ganti kata sandi</button></div>
                </form>
            </section>
        @endcan
    </div>
@endsection
