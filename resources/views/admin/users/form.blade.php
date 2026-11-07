@extends('layouts.app')

@php($ubah = $user->exists)

@section('title', ($ubah ? 'Ubah' : 'Tambah') . ' Pengguna — Lentera Aksara')

@section('content')
    <div class="container page">
        <a href="{{ route('admin.users.index') }}" class="back-link">← Kembali ke daftar pengguna</a>

        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Admin</span>
                <h1>{{ $ubah ? 'Ubah pengguna' : 'Tambah pengguna' }}</h1>
            </div>
        </div>

        @include('layouts.partials.flash')

        <section class="panel">
            <form method="POST" action="{{ $ubah ? route('admin.users.update', $user) : route('admin.users.store') }}" class="form-stack">
                @csrf
                @if ($ubah) @method('PUT') @endif

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
                    <input type="text" name="no_telepon" value="{{ old('no_telepon', $user->anggota?->no_telepon) }}">
                    @error('no_telepon') <span class="galat">{{ $message }}</span> @enderror
                </label>

                @can('kelola role pengguna')
                    <label class="field">
                        Role
                        <select name="role" required>
                            <option value="">Pilih role</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->name }}" @selected(old('role', $user->roles->first()?->name) === $r->name)>{{ ucfirst($r->name) }}</option>
                            @endforeach
                        </select>
                        @error('role') <span class="galat">{{ $message }}</span> @enderror
                    </label>
                @endcan

                <label class="field">
                    Kata sandi {{ $ubah ? '(kosongkan jika tidak diubah)' : '' }}
                    <input type="password" name="password" autocomplete="new-password" {{ $ubah ? '' : 'required' }}>
                    @error('password') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Konfirmasi kata sandi
                    <input type="password" name="password_confirmation" autocomplete="new-password">
                </label>

                <div class="aksi-baris">
                    <button type="submit" class="btn">{{ $ubah ? 'Simpan perubahan' : 'Tambah' }}</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-batal">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
