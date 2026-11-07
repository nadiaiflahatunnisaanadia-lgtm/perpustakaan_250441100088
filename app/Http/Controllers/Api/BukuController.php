<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BukuResource;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BukuController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $buku = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($request->filled('cari'), function ($query) use ($request) {
                $cari = $request->query('cari');

                $query->where(function ($q) use ($cari) {
                    $q->where('judul', 'like', "%{$cari}%")
                      ->orWhere('penulis', 'like', "%{$cari}%");
                });
            })
            ->when($request->filled('kategori'), fn ($query) => $query->where('kategori_id', $request->query('kategori')))
            ->when($request->filled('tahun'), fn ($query) => $query->where('tahun_terbit', $request->query('tahun')))
            ->latest()
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

     
        return BukuResource::collection($buku);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->aturan(), $this->pesan(), $this->atribut());

        $buku = Buku::create($data);
        $buku->load('kategori');

        return response()->json([
            'message' => 'Buku berhasil ditambahkan',
            'data'    => $buku,
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $buku = Buku::with('kategori')->find($id);

        if (! $buku) {
            return $this->tidakDitemukan();
        }

        // Data peminjaman milik orang lain hanya boleh dilihat staf pengelola peminjaman.
        if ($request->user()->can('kelola peminjaman')) {
            $buku->load('peminjamans');
        }

        return response()->json(['data' => $buku], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
      
        $aturan = array_map(fn (array $rule) => array_merge(['sometimes'], $rule), $this->aturan());

        $data = $request->validate($aturan, $this->pesan(), $this->atribut());

        $buku = Buku::find($id);

        if (! $buku) {
            return $this->tidakDitemukan();
        }

        $buku->update($data);

        return response()->json([
            'message' => 'Buku berhasil diperbarui',
            'data'    => $buku->fresh('kategori'),
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $buku = Buku::find($id);

        if (! $buku) {
            return $this->tidakDitemukan();
        }

        $buku->delete();

        return response()->json(['message' => 'Buku berhasil dihapus'], 200);
    }

    private function aturan(): array
    {
        return [
            'kategori_id'  => ['bail', 'required', 'integer', 'exists:kategoris,id'],
            'judul'        => ['bail', 'required', 'string', 'min:3', 'max:150'],
            'penulis'      => ['bail', 'required', 'string', 'min:3', 'max:100'],
            'tahun_terbit' => ['bail', 'required', 'integer', 'between:1900,' . date('Y')],
        ];
    }

    private function pesan(): array
    {
        return [
            'kategori_id.required' => 'Kategori wajib diisi.',
            'kategori_id.integer'  => 'Kategori yang dipilih tidak valid.',
            'kategori_id.exists'   => 'Kategori yang dipilih tidak valid.',

            'judul.required' => 'Judul buku wajib diisi.',
            'judul.string'   => 'Judul buku harus berupa teks.',
            'judul.min'      => 'Judul buku minimal 3 karakter',
            'judul.max'      => 'Judul buku maksimal 150 karakter',

            'penulis.required' => 'Penulis wajib diisi.',
            'penulis.string'   => 'Penulis harus berupa teks.',
            'penulis.min'      => 'Penulis minimal 3 karakter',
            'penulis.max'      => 'Penulis maksimal 100 karakter',

            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.integer'  => 'Tahun terbit harus berupa bilangan bulat.',
            'tahun_terbit.between'  => 'Tahun terbit harus antara 1900 sampai ' . date('Y'),
        ];
    }

    private function atribut(): array
    {
        return [
            'kategori_id'  => 'kategori',
            'judul'        => 'judul',
            'penulis'      => 'penulis',
            'tahun_terbit' => 'tahun terbit',
        ];
    }

    private function tidakDitemukan(): JsonResponse
    {
        return response()->json(['message' => 'not found'], 404);
    }
}
