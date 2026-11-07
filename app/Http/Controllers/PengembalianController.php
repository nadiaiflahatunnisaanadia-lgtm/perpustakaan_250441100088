<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengembalianController extends Controller
{
    public function index(): View
    {
        return view('pengembalian.index', [
            'dipinjam'     => Peminjaman::with(['anggota', 'buku'])
                ->where('status', Peminjaman::DIPINJAM)->oldest('tanggal_pinjam')->get(),
            'dikembalikan' => Peminjaman::with(['anggota', 'buku'])
                ->where('status', Peminjaman::DIKEMBALIKAN)->latest('tanggal_kembali')->latest('id')->take(10)->get(),
        ]);
    }

    public function store(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status !== Peminjaman::DIPINJAM) {
            return back()->with('gagal', 'Buku ini tidak sedang dipinjam.');
        }

        $peminjaman->update([
            'status'          => Peminjaman::DIKEMBALIKAN,
            'tanggal_kembali' => now()->toDateString(),
            'diproses_oleh'   => $request->user()->id,
        ]);

        return back()->with('sukses', 'Pengembalian buku berhasil dicatat.');
    }
}
