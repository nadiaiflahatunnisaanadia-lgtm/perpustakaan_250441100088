<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{

    public const PERMISSIONS = [
        'akses dashboard admin',               
        'akses dashboard petugas',             
        'lihat daftar buku',                   
        'kelola data buku',                    
        'kelola data pengguna',                
        'kelola role pengguna',               
        'kelola peminjaman',                  
        'kelola pengembalian',                
        'mengajukan peminjaman buku',          
        'memproses persetujuan peminjaman',    
        'mencatat pengembalian buku',         
        'melihat status peminjaman sendiri',   
        'melihat riwayat peminjaman sendiri',  
        'lihat laporan perpustakaan',          
        'lihat profil sendiri',                
        'ubah profil sendiri',                 
    ];

    public function run(): void
    {
        
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $nama) {
            Permission::findOrCreate($nama, 'web');
        }

        $dimiliki = [
            'admin' => [
                'akses dashboard admin',
                'lihat daftar buku',
                'kelola data buku',
                'kelola data pengguna',
                'kelola role pengguna',
                'kelola peminjaman',
                'kelola pengembalian',
                'mengajukan peminjaman buku',
                'memproses persetujuan peminjaman',
                'mencatat pengembalian buku',
                'melihat status peminjaman sendiri',
                'melihat riwayat peminjaman sendiri',
                'lihat laporan perpustakaan',
                'lihat profil sendiri',
                'ubah profil sendiri',
            ],
            'petugas' => [
                'akses dashboard petugas',
                'lihat daftar buku',
                'kelola data buku',
                'kelola peminjaman',
                'kelola pengembalian',
                'mengajukan peminjaman buku',
                'memproses persetujuan peminjaman',
                'mencatat pengembalian buku',
                'melihat status peminjaman sendiri',
                'melihat riwayat peminjaman sendiri',
                'lihat laporan perpustakaan',
                'lihat profil sendiri',
                'ubah profil sendiri',
            ],
            'siswa' => [
                'lihat daftar buku',
                'mengajukan peminjaman buku',
                'melihat status peminjaman sendiri',
                'melihat riwayat peminjaman sendiri',
                'lihat profil sendiri',
                'ubah profil sendiri',
            ],
        ];

        foreach ($dimiliki as $namaRole => $daftarPermission) {
            Role::findOrCreate($namaRole, 'web')->syncPermissions($daftarPermission);
        }
    }
}
