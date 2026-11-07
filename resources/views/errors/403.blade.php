@extends('layouts.app')

@section('title', 'Akses ditolak — Lentera Aksara')

@section('content')
    <div class="container page">
        <section class="empty-state">
            <span class="eyebrow">403 · Akses ditolak</span>
            <h1>Kamu tidak punya hak akses ke halaman ini.</h1>
            <p>Halaman atau tindakan yang kamu minta tidak diizinkan untuk role akunmu.</p>
            <a href="{{ auth()->check() ? auth()->user()->rutePendaratan() : route('home') }}" class="btn">Kembali</a>
        </section>
    </div>
@endsection
