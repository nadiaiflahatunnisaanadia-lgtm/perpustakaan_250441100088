@extends('layouts.app')

@section('title', 'Peminjaman — Lentera Aksara')

@section('content')
    <div class="container page">
        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">{{ $milikSendiri ? 'Peminjaman saya' : 'Riwayat anggota' }}</span>
                <h1>{{ $milikSendiri ? 'Peminjaman saya' : 'Riwayat ' . $anggota->nama }}</h1>
                <p>
                    {{ $milikSendiri
                        ? 'Status pengajuan dan riwayat peminjaman milikmu sendiri.'
                        : 'Data peminjaman anggota ini (hanya terlihat oleh staf perpustakaan).' }}
                </p>
            </div>
        </div>

        @include('layouts.partials.flash')

        @if (! $milikSendiri || auth()->user()->can('melihat status peminjaman sendiri'))
            <section class="panel">
                <h2>Status peminjaman</h2>
                @include('peminjaman._tabel-riwayat', ['daftar' => $aktif, 'kosong' => 'Tidak ada pengajuan atau peminjaman yang sedang berjalan.'])
            </section>
        @endif

        @if (! $milikSendiri || auth()->user()->can('melihat riwayat peminjaman sendiri'))
            <section class="panel">
                <h2>Riwayat peminjaman</h2>
                @include('peminjaman._tabel-riwayat', ['daftar' => $riwayat, 'kosong' => 'Belum ada riwayat peminjaman.'])
            </section>
        @endif
    </div>
@endsection
