@extends('layouts.app')

@section('title', ($buku ? 'Ubah Buku' : 'Tambah Buku') . ' — Lentera Aksara')

@section('content')
    <div class="container page">
        <a href="{{ route('buku.index') }}" class="back-link">← Kembali ke daftar buku</a>

        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Pengelolaan</span>
                <h1>{{ $buku ? 'Ubah buku' : 'Tambah buku' }}</h1>
                <p>Halaman ini hanya bisa dibuka admin dan petugas (Gate <em>kelola-buku</em>).</p>
            </div>
        </div>

        @include('layouts.partials.flash')

        <section class="panel">
            <form method="POST"
                  action="{{ $buku ? route('buku.update', $buku->id) : route('buku.store') }}"
                  class="form-stack">
                @csrf
                @if ($buku)
                    @method('PUT')
                @endif

                <label class="field">
                    Kategori
                    <select name="kategori_id" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id }}" @selected(old('kategori_id', $buku?->kategori_id) == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Judul
                    <input type="text" name="judul" value="{{ old('judul', $buku?->judul) }}" required autocomplete="off">
                    @error('judul') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Penulis
                    <input type="text" name="penulis" value="{{ old('penulis', $buku?->penulis) }}" required autocomplete="off">
                    @error('penulis') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    Tahun terbit
                    <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $buku?->tahun_terbit) }}" required>
                    @error('tahun_terbit') <span class="galat">{{ $message }}</span> @enderror
                </label>

                <div class="aksi-baris">
                    <button type="submit" class="btn">{{ $buku ? 'Simpan perubahan' : 'Tambah' }}</button>
                    <a href="{{ route('buku.index') }}" class="btn btn-batal">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
