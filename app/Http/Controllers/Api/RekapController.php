<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;

class RekapController extends Controller
{

    public function bukuPerKategori(): JsonResponse
    {
        $data = Kategori::select('id', 'nama')
            ->withCount('bukus as jumlah_buku')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'data' => $data,
        ], 200);
    }

    public function peminjamanPerBuku(): JsonResponse
    {
        $data = Buku::select('id', 'judul')
            ->withCount('peminjamans as total_peminjaman')
            ->orderByDesc('total_peminjaman')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $data,
        ], 200);
    }
}
