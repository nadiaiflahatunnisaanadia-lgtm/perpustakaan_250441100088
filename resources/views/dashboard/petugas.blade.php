@extends('layouts.app')

@section('title', 'Dashboard Petugas — Lentera Aksara')

@section('content')
    <div class="container page">
        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Petugas</span>
                <h1>Dashboard Petugas</h1>
                <p>Halo, {{ auth()->user()->name }}. Pantau operasional peminjaman dan pengembalian hari ini.</p>
            </div>
        </div>

        @include('layouts.partials.flash')

        <dl class="stat-grid">
            <div class="stat"><dt>Buku</dt><dd>{{ $ringkasan['buku'] }}</dd></div>
            <div class="stat"><dt>Anggota</dt><dd>{{ $ringkasan['anggota'] }}</dd></div>
            <div class="stat"><dt>Menunggu persetujuan</dt><dd>{{ $ringkasan['diajukan'] }}</dd></div>
            <div class="stat"><dt>Sedang dipinjam</dt><dd>{{ $ringkasan['dipinjam'] }}</dd></div>
        </dl>

        <section class="panel">
            <h2>Pintasan</h2>
            <div class="aksi-baris">
                @can('kelola data buku') <a class="btn" href="{{ route('buku.kelola') }}">Kelola buku</a> @endcan
                @can('kelola peminjaman') <a class="btn" href="{{ route('peminjaman.index') }}">Peminjaman</a> @endcan
                @can('kelola pengembalian') <a class="btn" href="{{ route('pengembalian.index') }}">Pengembalian</a> @endcan
                @can('lihat laporan perpustakaan') <a class="btn" href="{{ route('laporan.index') }}">Laporan</a> @endcan
            </div>
        </section>

        <section class="panel">
            <h2>Pengajuan menunggu persetujuan</h2>
            @include('peminjaman._tabel-ringkas', ['daftar' => $menunggu])
        </section>
    </div>
@endsection
