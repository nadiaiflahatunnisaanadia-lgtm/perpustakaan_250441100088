<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BukuController extends Controller
{
    /**
     * Katalog buku (hanya baca) untuk semua role yang punya permission
     * "lihat daftar buku". Halaman pengelolaan ada di kelola().
     */
    public function index(Request $request): View
    {
        $buku = Buku::with('kategori')
            ->when($request->filled('cari'), function ($query) use ($request) {
                $cari = $request->query('cari');
                $query->where(fn ($q) => $q->where('judul', 'like', "%{$cari}%")
                    ->orWhere('penulis', 'like', "%{$cari}%"));
            })
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori_id', $request->query('kategori')))
            ->orderBy('judul')
            ->paginate(9)
            ->withQueryString();

        return view('buku.index', [
            'buku'     => $buku,
            'kategori' => Kategori::orderBy('nama')->get(),
            'sampul'   => fn (Buku $b) => $this->pathSampul($b),
        ]);
    }

    /** Halaman pengelolaan data buku (tambah / ubah / hapus lewat API) — permission "kelola data buku". */
    public function kelola(): View
    {
        return view('buku.kelola', [
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function show($id)
    {
        $item = Buku::with('kategori')->find($id);

        return view('buku.show', [
            'buku' => $item ? $this->bentukData($item) : null,
        ]);
    }

    /**
     * SOAL 1 — CELAH A2 (sengaja rentan): SQL Injection.
     * Nilai query string "judul" digabungkan langsung ke string SQL tanpa binding.
     */
    public function cari(Request $request): JsonResponse
    {
        $judul = $request->query('judul');

        $hasil = DB::select("SELECT * FROM bukus WHERE judul = '$judul'");

        return response()->json($hasil);
    }

    private function bentukData(Buku $buku): array
    {
        return [
            'id'       => $buku->id,
            'judul'    => $buku->judul,
            'penulis'  => $buku->penulis,
            'tahun'    => $buku->tahun_terbit,
            'kategori' => $buku->kategori->nama ?? '-',
            'sampul'   => $this->pathSampul($buku),
        ];
    }

    private function pathSampul(Buku $buku): string
    {
        return 'sampul/' . Str::slug(Str::before($buku->judul, ':')) . '.jpg';
    }
}
