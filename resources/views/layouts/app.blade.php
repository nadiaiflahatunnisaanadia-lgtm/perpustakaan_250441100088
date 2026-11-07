<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Lentera Aksara')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    @vite('resources/css/app.css')
    @include('layouts.partials.ui')
</head>
<body>

    <header class="site-header">
        <div class="container nav-wrap">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark">LA</span>
                <span class="brand-text">
                    <strong>Lentera Aksara</strong>
                    <small>Rekomendasi bacaan pilihan</small>
                </span>
            </a>

            {{-- Menu ditampilkan sesuai hak akses (@can / @role dari Spatie) --}}
            <nav class="main-nav">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Beranda</a>

                @auth
                    @can('akses dashboard admin')
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Dashboard Admin</a>
                    @endcan
                    @can('akses dashboard petugas')
                        <a href="{{ route('petugas.dashboard') }}" class="{{ request()->routeIs('petugas.dashboard') ? 'is-active' : '' }}">Dashboard Petugas</a>
                    @endcan

                    @can('lihat daftar buku')
                        <a href="{{ route('buku.index') }}" class="{{ request()->routeIs('buku.index', 'buku.show') ? 'is-active' : '' }}">Daftar Buku</a>
                    @endcan
                    @can('kelola data buku')
                        <a href="{{ route('buku.kelola') }}" class="{{ request()->routeIs('buku.kelola') ? 'is-active' : '' }}">Kelola Buku</a>
                    @endcan

                    @can('kelola data pengguna')
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">Pengguna</a>
                    @endcan
                    @can('kelola peminjaman')
                        <a href="{{ route('peminjaman.index') }}" class="{{ request()->routeIs('peminjaman.index', 'peminjaman.create') ? 'is-active' : '' }}">Peminjaman</a>
                    @endcan
                    @can('kelola pengembalian')
                        <a href="{{ route('pengembalian.index') }}" class="{{ request()->routeIs('pengembalian.*') ? 'is-active' : '' }}">Pengembalian</a>
                    @endcan
                    @can('lihat laporan perpustakaan')
                        <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'is-active' : '' }}">Laporan</a>
                    @endcan

                    @canany(['melihat status peminjaman sendiri', 'melihat riwayat peminjaman sendiri'])
                        <a href="{{ route('peminjaman.saya') }}" class="{{ request()->routeIs('peminjaman.saya') ? 'is-active' : '' }}">Peminjaman Saya</a>
                    @endcanany
                    @can('lihat profil sendiri')
                        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'is-active' : '' }}">Profil</a>
                    @endcan
                @endauth
            </nav>

            <div class="nav-user">
                @auth
                    <span>{{ auth()->user()->name }}</span>
                    <span class="peran">
                        @role('admin') Admin @endrole
                        @role('petugas') Petugas @endrole
                        @role('siswa') Siswa @endrole
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-garis">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-garis">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-kecil">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <div class="container footer-wrap">
            <span>Lentera Aksara</span>
            <span>© 2026 &middot; Rekomendasi bacaan untuk kamu pilih berikutnya.</span>
        </div>
    </footer>

</body>
</html>
