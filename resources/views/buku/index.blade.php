@extends('layouts.app')

@section('title', 'Daftar Buku — Lentera Aksara')

@section('content')
    <div class="container page">

        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Rekomendasi</span>
                <h1>Buku yang layak kamu baca berikutnya.</h1>
                <p>
                    Pilihan buku dengan cerita tentang kehidupan,
                    dan perjalanan menemukan arah sendiri — dipilih untuk jadi referensi bacaanmu.
                </p>
            </div>

            <dl class="page-stats">
                <div>
                    <dt>Buku</dt>
                    <dd>{{ $buku->total() }}</dd>
                </div>
            </dl>
        </div>

        @include('layouts.partials.flash')

        <form method="GET" action="{{ route('buku.index') }}" class="filter-bar">
            <label class="field">
                Cari judul / penulis
                <input type="search" name="cari" value="{{ request('cari') }}">
            </label>

            <label class="field">
                Kategori
                <select name="kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $k)
                        <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="btn">Cari</button>
            @if (request()->hasAny(['cari', 'kategori']))
                <a href="{{ route('buku.index') }}" class="btn btn-batal">Reset</a>
            @endif

            {{-- SOAL 3: tombol Tambah hanya tampil bagi yang lolos Gate kelola-buku (admin & petugas) --}}
            @can('kelola-buku')
                <a href="{{ route('buku.create') }}" class="btn" style="margin-left: auto;">Tambah buku</a>
            @endcan
        </form>

        <section class="daftar-buku">
            @forelse ($buku as $item)
                <x-buku-card
                    :nomor="$item->id"
                    :judul="$item->judul"
                    :penulis="$item->penulis"
                    :tahun="$item->tahun_terbit"
                    :kategori="$item->kategori->nama ?? '-'"
                    :url="route('buku.show', $item->id)"
                    :sampul="$sampul($item)"
                >
                    @can('mengajukan peminjaman buku')
                        <form method="POST" action="{{ route('peminjaman.ajukan', $item->id) }}" class="aksi-kartu">
                            @csrf
                            <button type="submit" class="btn btn-kecil">Ajukan peminjaman</button>
                        </form>
                    @endcan

                    {{-- SOAL 3: tombol Ubah & Hapus hanya tampil bagi yang lolos Gate kelola-buku --}}
                    @can('kelola-buku')
                        <div class="aksi-kelola">
                            <a href="{{ route('buku.edit', $item->id) }}" class="btn btn-kecil">Ubah</a>

                            <form method="POST" action="{{ route('buku.destroy', $item->id) }}"
                                  onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-kecil btn-hapus">Hapus</button>
                            </form>
                        </div>
                    @endcan
                </x-buku-card>
            @empty
                <p style="grid-column: 1 / -1; color: var(--muted);">Tidak ada buku yang cocok dengan pencarianmu.</p>
            @endforelse
        </section>

        @if ($buku->hasPages())
            <div class="paginasi">
                @if ($buku->onFirstPage())
                    <span>← Sebelumnya</span>
                @else
                    <a href="{{ $buku->previousPageUrl() }}">← Sebelumnya</a>
                @endif

                <span class="cur">{{ $buku->currentPage() }}</span>
                <span>dari {{ $buku->lastPage() }}</span>

                @if ($buku->hasMorePages())
                    <a href="{{ $buku->nextPageUrl() }}">Berikutnya →</a>
                @else
                    <span>Berikutnya →</span>
                @endif
            </div>
        @endif
    </div>
@endsection
