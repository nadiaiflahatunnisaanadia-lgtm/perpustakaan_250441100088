@extends('layouts.app')

@section('title', 'Laporan — Lentera Aksara')

@section('content')
    <div class="container page">
        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Laporan</span>
                <h1>Laporan perpustakaan</h1>
                <p>Ringkasan koleksi dan aktivitas peminjaman.</p>
            </div>
        </div>

        <dl class="stat-grid">
            <div class="stat"><dt>Total buku</dt><dd>{{ $total['buku'] }}</dd></div>
            <div class="stat"><dt>Total anggota</dt><dd>{{ $total['anggota'] }}</dd></div>
            <div class="stat"><dt>Total peminjaman</dt><dd>{{ $total['peminjaman'] }}</dd></div>
            <div class="stat"><dt>Diajukan</dt><dd>{{ $total['diajukan'] }}</dd></div>
            <div class="stat"><dt>Dipinjam</dt><dd>{{ $total['dipinjam'] }}</dd></div>
            <div class="stat"><dt>Dikembalikan</dt><dd>{{ $total['dikembalikan'] }}</dd></div>
            <div class="stat"><dt>Ditolak</dt><dd>{{ $total['ditolak'] }}</dd></div>
        </dl>

        <section class="panel">
            <h2>Jumlah buku per kategori</h2>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead><tr><th>Kategori</th><th>Jumlah buku</th></tr></thead>
                    <tbody>
                        @forelse ($perKategori as $k)
                            <tr><td>{{ $k->nama }}</td><td>{{ $k->bukus_count }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="kosong">Belum ada kategori.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h2>10 buku paling sering dipinjam</h2>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead><tr><th>Judul</th><th>Total peminjaman</th></tr></thead>
                    <tbody>
                        @forelse ($perBuku as $b)
                            <tr><td>{{ $b->judul }}</td><td>{{ $b->peminjamans_count }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="kosong">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
