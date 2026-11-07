@extends('layouts.app')

@section('title', 'Pengembalian — Lentera Aksara')

@section('content')
    <div class="container page">
        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Operasional</span>
                <h1>Pengembalian buku</h1>
                <p>Catat pengembalian untuk buku yang sedang dipinjam.</p>
            </div>
        </div>

        @include('layouts.partials.flash')

        <section class="panel">
            <h2>Sedang dipinjam</h2>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr><th>Anggota</th><th>Buku</th><th>Tgl pinjam</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($dipinjam as $p)
                            <tr>
                                <td>{{ $p->anggota->nama ?? '-' }}</td>
                                <td>{{ $p->buku->judul ?? '-' }}</td>
                                <td>{{ $p->tanggal_pinjam?->translatedFormat('d M Y') }}</td>
                                <td>
                                    @can('mencatat pengembalian buku')
                                        <form method="POST" action="{{ route('pengembalian.store', $p) }}" class="inline-form"
                                              onsubmit="return confirm('Catat buku ini sebagai sudah dikembalikan?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-kecil btn-ok">Catat kembali</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="kosong">Tidak ada buku yang sedang dipinjam.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h2>Pengembalian terakhir</h2>
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr><th>Anggota</th><th>Buku</th><th>Tgl pinjam</th><th>Tgl kembali</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($dikembalikan as $p)
                            <tr>
                                <td>{{ $p->anggota->nama ?? '-' }}</td>
                                <td>{{ $p->buku->judul ?? '-' }}</td>
                                <td>{{ $p->tanggal_pinjam?->translatedFormat('d M Y') }}</td>
                                <td>{{ $p->tanggal_kembali?->translatedFormat('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="kosong">Belum ada pengembalian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
