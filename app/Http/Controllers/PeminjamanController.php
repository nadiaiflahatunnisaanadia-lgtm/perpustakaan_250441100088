<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function index(Request $request): View
    {
        $peminjaman = Peminjaman::with(['anggota', 'buku', 'pemroses'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('peminjaman.index', [
            'peminjaman' => $peminjaman,
            'status'     => $request->query('status'),
        ]);
    }

  
    public function show($id): View
    {
        $peminjaman = Peminjaman::with(['anggota', 'buku.kategori', 'pemroses'])->findOrFail($id);

        Gate::authorize('view', $peminjaman);

        return view('peminjaman.show', ['peminjaman' => $peminjaman]);
    }

    public function create(): View
    {
        return view('peminjaman.create', [
            'anggotas' => Anggota::orderBy('nama')->get(),
            'bukus'    => Buku::orderBy('judul')->get(),
        ]);
    }

    
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'anggota_id' => ['required', 'integer', 'exists:anggotas,id'],
            'buku_id'    => ['required', 'integer', 'exists:bukus,id'],
        ], [
            'anggota_id.required' => 'Anggota wajib dipilih.',
            'anggota_id.exists'   => 'Anggota tidak valid.',
            'buku_id.required'    => 'Buku wajib dipilih.',
            'buku_id.exists'      => 'Buku tidak valid.',
        ]);

        $sudahAda = Peminjaman::where('anggota_id', $data['anggota_id'])
            ->where('buku_id', $data['buku_id'])->aktif()->exists();

        if ($sudahAda) {
            return back()->withInput()->with('gagal', 'Anggota ini masih punya pengajuan/peminjaman aktif untuk buku tersebut.');
        }

        Peminjaman::create($data + [
            'tanggal_pinjam' => now()->toDateString(),
            'status'         => Peminjaman::DIPINJAM,
            'diproses_oleh'  => $request->user()->id,
        ]);

        return redirect()->route('peminjaman.index')->with('sukses', 'Peminjaman berhasil dicatat.');
    }

    public function setujui(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status !== Peminjaman::DIAJUKAN) {
            return back()->with('gagal', 'Hanya pengajuan yang berstatus "diajukan" yang bisa diproses.');
        }

        $peminjaman->update([
            'status'         => Peminjaman::DIPINJAM,
            'tanggal_pinjam' => now()->toDateString(),
            'diproses_oleh'  => $request->user()->id,
        ]);

        return back()->with('sukses', 'Pengajuan peminjaman disetujui.');
    }

    public function tolak(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status !== Peminjaman::DIAJUKAN) {
            return back()->with('gagal', 'Hanya pengajuan yang berstatus "diajukan" yang bisa diproses.');
        }

        $peminjaman->update([
            'status'        => Peminjaman::DITOLAK,
            'diproses_oleh' => $request->user()->id,
        ]);

        return back()->with('sukses', 'Pengajuan peminjaman ditolak.');
    }
}
