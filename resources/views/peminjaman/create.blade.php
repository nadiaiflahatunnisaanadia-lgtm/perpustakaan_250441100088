@extends('layouts.app')

@section('title', 'Catat Peminjaman — Lentera Aksara')

@section('content')
    <div class="container page">
        <a href="{{ route('peminjaman.index') }}" class="back-link">← Kembali ke data peminjaman</a>

        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Operasional</span>
                <h1>Catat peminjaman</h1>
                <p>Untuk peminjam yang datang langsung ke perpustakaan. Status langsung menjadi “dipinjam”.</p>
            </div>
        </div>

        @include('layouts.partials.flash')

        <section class="panel">
            <form method="POST" action="{{ route('peminjaman.store') }}" class="form-stack">
                @csrf

                <label class="field">
                    Anggota
                    <select name="anggota_id" required>
                        <option value="">Pilih anggota</option>
                        @foreach ($anggotas as $a)
                            <option value="{{ $a->id }}" @selected(old('anggota_id') == $a->id)>{{ $a->nama }} — {{ $a->email }}</option>
                        @endforeach
                    </select>
                    @error('anggota_id') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Buku
                    <select name="buku_id" required>
                        <option value="">Pilih buku</option>
                        @foreach ($bukus as $b)
                            <option value="{{ $b->id }}" @selected(old('buku_id') == $b->id)>{{ $b->judul }}</option>
                        @endforeach
                    </select>
                    @error('buku_id') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <div class="aksi-baris">
                    <button type="submit" class="btn">Simpan</button>
                    <a href="{{ route('peminjaman.index') }}" class="btn btn-batal">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
