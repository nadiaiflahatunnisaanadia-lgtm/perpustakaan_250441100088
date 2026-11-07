<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PeminjamanSayaController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('home');
})->name('home');


require __DIR__.'/auth.php';


Route::middleware('auth')->group(function () {

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('permission:akses dashboard admin')
        ->name('admin.dashboard');

    Route::get('/petugas/dashboard', [DashboardController::class, 'petugas'])
        ->middleware('permission:akses dashboard petugas')
        ->name('petugas.dashboard');


    Route::middleware('permission:lihat daftar buku')->group(function () {
        Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');
        Route::get('/buku/{id}', [BukuController::class, 'show'])->whereNumber('id')->name('buku.show');
    });

 
    // SOAL 3: seluruh pengelolaan buku (halaman kelola, tambah, ubah, hapus) dijaga Gate "kelola-buku"
    Route::middleware('can:kelola-buku')->group(function () {
        Route::get('/kelola/buku', [BukuController::class, 'kelola'])->name('buku.kelola');

        Route::get('/buku/create', [BukuController::class, 'create'])->name('buku.create');
        Route::post('/buku', [BukuController::class, 'store'])->name('buku.store');
        Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])->whereNumber('buku')->name('buku.edit');
        Route::put('/buku/{buku}', [BukuController::class, 'update'])->whereNumber('buku')->name('buku.update');
        Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])->whereNumber('buku')->name('buku.destroy');
    });

    Route::post('/buku/{buku}/ajukan', [PengajuanController::class, 'store'])
        ->middleware('permission:mengajukan peminjaman buku')
        ->whereNumber('buku')
        ->name('peminjaman.ajukan');

    Route::get('/peminjaman-saya', [PeminjamanSayaController::class, 'saya'])
        ->middleware('permission:melihat status peminjaman sendiri|melihat riwayat peminjaman sendiri')
        ->name('peminjaman.saya');


    Route::get('/riwayat/{anggota}', [PeminjamanSayaController::class, 'riwayat'])
        ->whereNumber('anggota')
        ->name('riwayat.show');

   
    Route::middleware('permission:kelola peminjaman')->group(function () {
        Route::get('/kelola/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('/kelola/peminjaman/tambah', [PeminjamanController::class, 'create'])->name('peminjaman.create');
        Route::post('/kelola/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');

        Route::middleware('permission:memproses persetujuan peminjaman')->group(function () {
            Route::patch('/kelola/peminjaman/{peminjaman}/setujui', [PeminjamanController::class, 'setujui'])->name('peminjaman.setujui');
            Route::patch('/kelola/peminjaman/{peminjaman}/tolak', [PeminjamanController::class, 'tolak'])->name('peminjaman.tolak');
        });
    });

    Route::middleware('permission:kelola pengembalian')->group(function () {
        Route::get('/kelola/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');

        Route::patch('/kelola/pengembalian/{peminjaman}', [PengembalianController::class, 'store'])
            ->middleware('permission:mencatat pengembalian buku')
            ->name('pengembalian.store');
    });

    Route::get('/laporan', [LaporanController::class, 'index'])
        ->middleware('permission:lihat laporan perpustakaan')
        ->name('laporan.index');

    // SOAL 2 (b): pencarian buku memakai query dengan binding
    Route::get('/buku-cari', [BukuController::class, 'cari'])->name('buku.cari');

    // A4: IDOR — tidak ada pemeriksaan pemilik pada peminjaman (ditutup pada Soal 4)
    Route::get('/peminjaman/{id}', [PeminjamanController::class, 'show'])
        ->whereNumber('id')
        ->name('peminjaman.show');

    Route::get('/profil', [ProfileController::class, 'edit'])
        ->middleware('permission:lihat profil sendiri')
        ->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])
        ->middleware('permission:ubah profil sendiri')
        ->name('profile.update');

    Route::prefix('admin/users')->name('admin.users.')
        ->middleware('permission:kelola data pengguna')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/tambah', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/ubah', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });
});
