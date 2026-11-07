@extends('layouts.app')

@section('title', 'Kelola Pengguna — Lentera Aksara')

@section('content')
    <div class="container page">
        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Admin</span>
                <h1>Kelola pengguna &amp; role</h1>
                <p>Tambah akun, ubah data, dan tetapkan role. Hanya admin yang bisa membuka halaman ini.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn">Tambah pengguna</a>
        </div>

        @include('layouts.partials.flash')

        <section class="panel">
            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $u)
                            <tr>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                                <td>
                                    <div class="aksi-baris">
                                        <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-kecil">Ubah</a>
                                        @if (! $u->is(auth()->user()))
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline-form"
                                                  onsubmit="return confirm('Hapus pengguna {{ addslashes($u->name) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-kecil btn-hapus">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="kosong">Belum ada pengguna.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="paginasi">
                    @if (! $users->onFirstPage()) <a href="{{ $users->previousPageUrl() }}">← Sebelumnya</a> @endif
                    <span class="cur">{{ $users->currentPage() }}</span> <span>dari {{ $users->lastPage() }}</span>
                    @if ($users->hasMorePages()) <a href="{{ $users->nextPageUrl() }}">Berikutnya →</a> @endif
                </div>
            @endif
        </section>
    </div>
@endsection
