# Modul 5 — Autentikasi & RBAC (Sistem Perpustakaan Digital)

Lanjutan proyek Modul 4. Fitur lama (katalog, detail buku, API buku, rekap) dipertahankan;
ditambah autentikasi (Breeze), session + middleware, serta RBAC dengan Spatie Laravel Permission.

## 1. Cara menjalankan

```bash
# 1) pasang paket baru (ini yang memperbarui composer.json & composer.lock)
composer require spatie/laravel-permission
composer require laravel/breeze --dev        # opsional, lihat catatan Breeze di bawah

# 2) bersihkan cache lalu migrasi + seed (role, permission, akun contoh, data)
php artisan optimize:clear
php artisan migrate:fresh --seed             # atau: php artisan migrate && php artisan db:seed

# 3) jalankan
php artisan serve
```

> `config/permission.php` dan migrasi tabel Spatie sudah ada di proyek ini (hasil `vendor:publish`),
> jadi tidak perlu dipublish ulang. **Jangan** menjalankan `php artisan breeze:install` — itu akan
> menimpa layout/CSS Lentera Aksara dan file auth yang sudah disesuaikan.

### Akun contoh (password `password`; **khusus admin sudah diganti di Modul 6**, lihat `README_MODUL6.md`)

| Role    | Email                        |
|---------|------------------------------|
| Admin   | admin@perpustakaan.test      |
| Petugas | petugas@perpustakaan.test    |
| Siswa   | siswa1@perpustakaan.test     |
| Siswa   | siswa2@perpustakaan.test     |

Role hanya ditetapkan oleh seeder atau admin (menu **Pengguna**). Registrasi `/register` selalu
membuat role **siswa**; field `role` yang dikirim lewat form diabaikan.

## 2. Ringkasan perubahan

| Soal | Implementasi |
|------|--------------|
| 1 | `routes/auth.php`, `Auth/*Controller`, `LoginRequest`, view `auth/login` & `auth/register`. Password di-hash (`Hash::make` + cast `hashed`). Registrasi membuat `users` **dan** `anggotas` (terhubung lewat `anggotas.user_id`). |
| 2 | `auth` melindungi semua halaman internal; `guest` pada login/register (user yang sudah login dialihkan ke halaman sesuai role). Logout = `session()->invalidate()` + `regenerateToken()`. |
| 3 & 4 | `RolePermissionSeeder`: 3 role, 16 permission, pembagian sesuai tabel soal. `User` memakai trait `HasRoles`. |
| 5 | `routes/web.php` & `routes/api.php`: middleware `auth` + `permission:...`. Menu & tombol di Blade memakai `@can`, `@canany`, `@role`. Halaman 403 kustom. |

Tambahan agar fitur peminjaman benar-benar ada:
* migrasi `anggotas.user_id` (akun ↔ anggota) dan `peminjamans.status` / `diproses_oleh`
  (alur `diajukan → dipinjam → dikembalikan`, atau `ditolak`); data lama otomatis di-backfill statusnya;
* halaman: dashboard admin/petugas, kelola pengguna & role, peminjaman, pengembalian, laporan,
  peminjaman saya, profil.

### Pembatasan data pribadi (berdasarkan identitas, bukan hanya permission)
* **Profil**: route `/profil` tanpa `{id}`; selalu memakai `$request->user()`.
* **Riwayat**: `/riwayat/{anggota}` dicek `AnggotaPolicy@lihatRiwayat` — pemilik data, atau staf
  yang punya `kelola peminjaman`. Siswa lain → 403.
* **Mengajukan pinjam**: anggota diambil dari user login, tidak ada input `anggota_id`.
* `GET /api/buku/{id}` hanya menyertakan data `peminjamans` untuk staf.

### Perubahan pada API Modul 4 (perlu diketahui)
Semua route `/api/*` sekarang memakai middleware `web` + `auth` + `permission`, sehingga
tanpa login → **401**, tanpa hak → **403**. Konsekuensinya, klien tanpa session (mis. Postman
tanpa cookie) tidak bisa lagi memanggil API; request tulis dari browser harus membawa token CSRF
(halaman *Kelola Buku* sudah mengirimnya). Halaman `/buku` lama yang berisi CRUD dipindah ke
`/kelola/buku`; `/buku` kini katalog read-only.

### Catatan Breeze
Controller/request autentikasi diambil dari stub resmi Breeze 2.x (`LoginRequest`,
`AuthenticatedSessionController`, `RegisteredUserController`, `PasswordController`) lalu disesuaikan
(redirect per role, pembuatan anggota, pesan Indonesia, view memakai layout proyek). Fitur
lupa-password dan verifikasi email tidak diaktifkan.

## 3. Hasil pengujian (soal nomor 5)

Diverifikasi dengan `php artisan test` (35 tes, 219 assertion — `tests/Feature/RbacTest.php`) dan
uji HTTP langsung (curl + cookie + CSRF sungguhan) terhadap `php artisan serve`.
Ulangi di browser untuk screenshot laporan.

| No | Role | Skenario | Diharapkan | Hasil Aktual |
|----|------|----------|-----------|--------------|
| 1 | Belum login | Buka dashboard admin | Dialihkan ke login | Redirect 302 ke `/login` |
| 2 | Admin | Buka dashboard admin | Dapat diakses | 200 OK |
| 3 | Admin | Tambah, ubah, hapus buku | Operasi berhasil | POST 201, PUT 200, DELETE 200; data di DB ikut berubah |
| 4 | Admin | Kelola akun dan role | Operasi berhasil | Tambah, ubah role, hapus pengguna berhasil |
| 5 | Admin | Akses peminjaman, pengembalian, laporan | Dapat diakses | `/kelola/peminjaman`, `/kelola/pengembalian`, `/laporan` → 200 |
| 6 | Petugas | Buka dashboard petugas | Dapat diakses | 200 OK (dashboard admin → 403) |
| 7 | Petugas | Kelola data buku | Operasi berhasil | `/kelola/buku` 200; POST `/api/buku` 201 |
| 8 | Petugas | Proses peminjaman & pengembalian | Operasi berhasil | Setujui → `dipinjam`; catat kembali → `dikembalikan` |
| 9 | Petugas | Buka kelola akun/role | Akses ditolak | 403 (index, tambah, simpan, ubah, hapus) |
| 10 | Siswa | Lihat daftar & detail buku | Dapat dilihat | `/buku` dan `/buku/{id}` → 200 |
| 11 | Siswa | Lihat & ubah profil sendiri | Dapat diakses & diperbarui | `/profil` 200; PATCH berhasil (nama & telepon berubah) |
| 12 | Siswa | Lihat riwayat sendiri | Dapat dilihat | `/peminjaman-saya` & `/riwayat/{id sendiri}` → 200 |
| 13 | Siswa | Buka dashboard admin / kelola buku | Akses ditolak | 403 (juga POST `/api/buku` → 403) |
| 14 | Siswa | Lihat riwayat siswa lain | Akses ditolak | `/riwayat/{id siswa lain}` → 403 |
| 15 | Semua | Logout lalu buka halaman terlindungi | Harus login lagi | Redirect 302 ke `/login` |

## 4. Matriks permission

Lihat `database/seeders/RolePermissionSeeder.php`; kesesuaiannya dengan tabel soal diuji otomatis
(`test_matriks_permission_sesuai_tabel_soal`).
