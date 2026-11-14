<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBukuRequest;
use App\Http\Requests\UpdateBukuRequest;
use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BukuController extends Controller
{
    
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

    
    public function kelola(): View
    {
        return view('buku.kelola', [
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

  
    public function create(): View
    {
        return view('buku.form', [
            'buku'     => null,
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function store(StoreBukuRequest $request): RedirectResponse
    {
        Buku::create($request->validated());

        return redirect()->route('buku.index')->with('sukses', 'Buku berhasil ditambahkan.');
    }

   
    public function edit(Buku $buku): View
    {
        return view('buku.form', [
            'buku'     => $buku,
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function update(UpdateBukuRequest $request, Buku $buku): RedirectResponse
    {
        $buku->update($request->validated());

        return redirect()->route('buku.index')->with('sukses', 'Buku berhasil diperbarui.');
    }

    public function show($id)
    {
        $item = Buku::with('kategori')->find($id);

        return view('buku.show', [
            'buku' => $item ? $this->bentukData($item) : null,
        ]);
    }

   
    public function cari(Request $request): JsonResponse
    {
        $judul = (string) $request->query('judul', '');

        $hasil = DB::select('SELECT * FROM bukus WHERE judul = ?', [$judul]);

        return response()->json($hasil);
    }

    
    public function destroy(Buku $buku): RedirectResponse
    {
        $buku->delete();

        return back()->with('sukses', 'Buku berhasil dihapus.');
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
