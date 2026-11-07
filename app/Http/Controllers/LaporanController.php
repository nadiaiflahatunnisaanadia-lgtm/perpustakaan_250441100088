<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(): View
    {
        return view('laporan.index', [
            'total' => [
                'buku'         => Buku::count(),
                'anggota'      => Anggota::count(),
                'peminjaman'   => Peminjaman::count(),
                'diajukan'     => Peminjaman::where('status', Peminjaman::DIAJUKAN)->count(),
                'dipinjam'     => Peminjaman::where('status', Peminjaman::DIPINJAM)->count(),
                'dikembalikan' => Peminjaman::where('status', Peminjaman::DIKEMBALIKAN)->count(),
                'ditolak'      => Peminjaman::where('status', Peminjaman::DITOLAK)->count(),
            ],
            'perKategori' => Kategori::withCount('bukus')->orderBy('nama')->get(),
            'perBuku'     => Buku::withCount('peminjamans')->orderByDesc('peminjamans_count')->orderBy('judul')->take(10)->get(),
        ]);
    }
}
