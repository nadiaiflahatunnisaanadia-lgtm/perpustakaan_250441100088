<?php

use App\Http\Controllers\Api\BukuController;
use App\Http\Controllers\Api\RekapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API (Modul 4) — sekarang dilindungi login + permission (Modul 5)
|--------------------------------------------------------------------------
| Middleware "web" ditambahkan agar session login dipakai juga oleh fetch()
| dari halaman web (same-origin) dan token CSRF ikut diperiksa untuk
| POST/PUT/DELETE. Permintaan tanpa login mendapat 401, tanpa hak akses 403.
*/
Route::middleware(['web', 'auth'])->group(function () {

    // Baca data buku: semua role yang boleh melihat daftar buku
    Route::middleware('permission:lihat daftar buku')->group(function () {
        Route::get('buku', [BukuController::class, 'index'])->name('api.buku.index');
        Route::get('buku/{id}', [BukuController::class, 'show'])->name('api.buku.show');
    });

    // Tambah / ubah / hapus buku: dijaga Gate "kelola-buku" (admin & petugas) — Soal 3
    Route::middleware('can:kelola-buku')->group(function () {
        Route::post('buku', [BukuController::class, 'store'])->name('api.buku.store');
        Route::match(['put', 'patch'], 'buku/{id}', [BukuController::class, 'update'])->name('api.buku.update');
        Route::delete('buku/{id}', [BukuController::class, 'destroy'])->name('api.buku.destroy');
    });

    // Rekap / laporan: admin & petugas
    Route::middleware('permission:lihat laporan perpustakaan')->group(function () {
        Route::get('rekap/buku-per-kategori', [RekapController::class, 'bukuPerKategori'])
            ->name('api.rekap.buku-per-kategori');

        Route::get('rekap/peminjaman-per-buku', [RekapController::class, 'peminjamanPerBuku'])
            ->name('api.rekap.peminjaman-per-buku');
    });
});
