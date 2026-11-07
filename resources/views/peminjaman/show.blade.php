@extends('layouts.app')

@section('title', 'Detail Peminjaman — Lentera Aksara')

@section('content')
    <div class="container page">
        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Detail peminjaman</span>
                <h1>Peminjaman #{{ $peminjaman->id }}</h1>
                <p>Rincian satu transaksi peminjaman buku.</p>
            </div>
        </div>

        <section class="panel">
            <div class="tabel-wrap">
                <table class="tabel">
                    <tbody>
                        <tr><th>ID peminjaman</th><td>{{ $peminjaman->id }}</td></tr>
                        <tr><th>Anggota</th><td>{{ $peminjaman->anggota->nama ?? '-' }}</td></tr>
                        <tr><th>Email anggota</th><td>{{ $peminjaman->anggota->email ?? '-' }}</td></tr>
                        <tr><th>No. telepon</th><td>{{ $peminjaman->anggota->no_telepon ?? '-' }}</td></tr>
                        <tr><th>Buku</th><td>{{ $peminjaman->buku->judul ?? '-' }}</td></tr>
                        <tr><th>Penulis</th><td>{{ $peminjaman->buku->penulis ?? '-' }}</td></tr>
                        <tr><th>Kategori</th><td>{{ $peminjaman->buku->kategori->nama ?? '-' }}</td></tr>
                        <tr><th>Tanggal pinjam</th><td>{{ $peminjaman->tanggal_pinjam?->translatedFormat('d M Y') ?? '-' }}</td></tr>
                        <tr><th>Tanggal kembali</th><td>{{ $peminjaman->tanggal_kembali?->translatedFormat('d M Y') ?? '-' }}</td></tr>
                        <tr><th>Status</th><td><span class="status status-{{ $peminjaman->status }}">{{ $peminjaman->status }}</span></td></tr>
                        <tr><th>Diproses oleh</th><td>{{ $peminjaman->pemroses->name ?? '-' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
