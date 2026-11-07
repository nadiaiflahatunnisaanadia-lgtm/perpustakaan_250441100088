<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    /**
     * Mengajukan peminjaman buku. Peminjam SELALU akun yang sedang login:
     * tidak ada input anggota_id dari form, jadi tidak bisa mengajukan atas nama orang lain.
     */
    public function store(Request $request, Buku $buku): RedirectResponse
    {
        $anggota = $request->user()->anggotaAtauBuat();

        $sudahAda = Peminjaman::where('anggota_id', $anggota->id)
            ->where('buku_id', $buku->id)
            ->aktif()
            ->exists();

        if ($sudahAda) {
            return back()->with('gagal', 'Kamu masih punya pengajuan/peminjaman aktif untuk buku ini.');
        }

        Peminjaman::create([
            'anggota_id'     => $anggota->id,
            'buku_id'        => $buku->id,
            'tanggal_pinjam' => now()->toDateString(),
            'status'         => Peminjaman::DIAJUKAN,
        ]);

        return back()->with('sukses', "Pengajuan peminjaman \"{$buku->judul}\" berhasil dikirim. Tunggu persetujuan petugas.");
    }
}
