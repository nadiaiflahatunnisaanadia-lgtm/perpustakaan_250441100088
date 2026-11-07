<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin', [
            'ringkasan' => $this->ringkasan() + ['pengguna' => User::count()],
            'terbaru'   => Peminjaman::with(['anggota', 'buku'])->latest()->take(5)->get(),
        ]);
    }

    public function petugas(): View
    {
        return view('dashboard.petugas', [
            'ringkasan' => $this->ringkasan(),
            'menunggu'  => Peminjaman::with(['anggota', 'buku'])
                ->where('status', Peminjaman::DIAJUKAN)->oldest()->take(5)->get(),
        ]);
    }

    private function ringkasan(): array
    {
        return [
            'buku'     => Buku::count(),
            'anggota'  => Anggota::count(),
            'diajukan' => Peminjaman::where('status', Peminjaman::DIAJUKAN)->count(),
            'dipinjam' => Peminjaman::where('status', Peminjaman::DIPINJAM)->count(),
        ];
    }
}
