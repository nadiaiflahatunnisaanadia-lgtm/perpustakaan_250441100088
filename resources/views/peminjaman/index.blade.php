@extends('layouts.app')

@section('title', 'Peminjaman — Lentera Aksara')

@section('content')
    <div class="container page">
        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Operasional</span>
                <h1>Data peminjaman</h1>
                <p>Proses pengajuan peminjaman: setujui atau tolak. Pengembalian dicatat di menu Pengembalian.</p>
            </div>
            <a href="{{ route('peminjaman.create') }}" class="btn">Catat peminjaman</a>
        </div>

        @include('layouts.partials.flash')

        <form method="GET" action="{{ route('peminjaman.index') }}" class="filter-bar">
            <label class="field">
                Status
                <select name="status">
                    <option value="">Semua</option>
                    @foreach (['diajukan', 'dipinjam', 'dikembalikan', 'ditolak'] as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn">Terapkan</button>
        </form>

        <section class="panel">
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr><th>Anggota</th><th>Buku</th><th>Tgl pinjam</th><th>Tgl kembali</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($peminjaman as $p)
                            <tr>
                                <td><a href="{{ route('riwayat.show', $p->anggota_id) }}">{{ $p->anggota->nama ?? '-' }}</a></td>
                                <td>{{ $p->buku->judul ?? '-' }}</td>
                                <td>{{ $p->tanggal_pinjam?->translatedFormat('d M Y') }}</td>
                                <td>{{ $p->tanggal_kembali?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td><span class="status status-{{ $p->status }}">{{ $p->status }}</span></td>
                                <td>
                                    @if ($p->status === 'diajukan')
                                        @can('memproses persetujuan peminjaman')
                                            <div class="aksi-baris">
                                                <form method="POST" action="{{ route('peminjaman.setujui', $p) }}" class="inline-form">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-kecil btn-ok">Setujui</button>
                                                </form>
                                                <form method="POST" action="{{ route('peminjaman.tolak', $p) }}" class="inline-form">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-kecil btn-hapus">Tolak</button>
                                                </form>
                                            </div>
                                        @endcan
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="kosong">Belum ada data peminjaman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($peminjaman->hasPages())
                <div class="paginasi">
                    @if (! $peminjaman->onFirstPage()) <a href="{{ $peminjaman->previousPageUrl() }}">← Sebelumnya</a> @endif
                    <span class="cur">{{ $peminjaman->currentPage() }}</span> <span>dari {{ $peminjaman->lastPage() }}</span>
                    @if ($peminjaman->hasMorePages()) <a href="{{ $peminjaman->nextPageUrl() }}">Berikutnya →</a> @endif
                </div>
            @endif
        </section>
    </div>
@endsection
