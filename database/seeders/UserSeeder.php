<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
  
    public function run(): void
    {
        // SOAL 5 (c): password admin TIDAK lagi "password". Diambil dari ADMIN_PASSWORD di .env;
        // bila kosong dibuat acak (dan ditampilkan sekali di konsol).
        $passwordAdmin = config('app.admin_password');

        if (blank($passwordAdmin)) {
            $passwordAdmin = Str::password(20, symbols: false);
            $this->command?->warn("ADMIN_PASSWORD kosong. Password admin acak: {$passwordAdmin}");
        }

        $akun = [
            ['Admin Perpustakaan',   'admin@perpustakaan.test',   'admin',   $passwordAdmin],
            ['Petugas Perpustakaan', 'petugas@perpustakaan.test', 'petugas', 'password'],
            ['Siswa Satu',           'siswa1@perpustakaan.test',  'siswa',   'password'],
            ['Siswa Dua',            'siswa2@perpustakaan.test',  'siswa',   'password'],
        ];

        foreach ($akun as [$nama, $email, $role, $password]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $nama, 'password' => Hash::make($password)],
            );
            $user->syncRoles($role);

            $anggota = Anggota::where('user_id', $user->id)->first()
                ?? Anggota::whereNull('user_id')->where('email', $email)->first()
                ?? new Anggota();

            $anggota->fill([
                'user_id'    => $user->id,
                'nama'       => $nama,
                'email'      => $email,
                'no_telepon' => $anggota->no_telepon ?? '081234567890',
            ])->save();
        }

        if (Buku::count() >= 3) {
            foreach (['siswa1@perpustakaan.test', 'siswa2@perpustakaan.test'] as $i => $email) {
                $anggota = User::where('email', $email)->first()->anggota;

                if ($anggota->peminjamans()->exists()) {
                    continue;
                }

                $buku = Buku::orderBy('id')->skip($i * 3)->take(3)->get();

                Peminjaman::create([
                    'anggota_id' => $anggota->id, 'buku_id' => $buku[0]->id,
                    'tanggal_pinjam' => now()->subDays(20), 'tanggal_kembali' => now()->subDays(8),
                    'status' => Peminjaman::DIKEMBALIKAN,
                ]);
                Peminjaman::create([
                    'anggota_id' => $anggota->id, 'buku_id' => $buku[1]->id,
                    'tanggal_pinjam' => now()->subDays(3), 'status' => Peminjaman::DIPINJAM,
                ]);
                Peminjaman::create([
                    'anggota_id' => $anggota->id, 'buku_id' => $buku[2]->id,
                    'tanggal_pinjam' => now(), 'status' => Peminjaman::DIAJUKAN,
                ]);
            }
        }
    }
}
