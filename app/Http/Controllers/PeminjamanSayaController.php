<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PeminjamanSayaController extends Controller
{
    
    public function saya(Request $request): View
    {
        $anggota = $request->user()->anggotaAtauBuat();

        return $this->tampilkan($anggota, milikSendiri: true);
    }

   
    public function riwayat(Anggota $anggota): View
    {
        Gate::authorize('lihatRiwayat', $anggota);

        return $this->tampilkan($anggota, milikSendiri: auth()->id() === $anggota->user_id);
    }

    private function tampilkan(Anggota $anggota, bool $milikSendiri): View
    {
        $dasar = Peminjaman::with('buku.kategori')->where('anggota_id', $anggota->id);

        return view('peminjaman.riwayat', [
            'anggota'      => $anggota,
            'milikSendiri' => $milikSendiri,
            'aktif'        => (clone $dasar)->aktif()->latest()->get(),
            'riwayat'      => (clone $dasar)->riwayat()->latest()->get(),
        ]);
    }
}
