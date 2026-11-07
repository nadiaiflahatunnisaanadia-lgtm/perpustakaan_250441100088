# Modul 6 — Keamanan Web dan Otorisasi (Sistem Perpustakaan Digital)

Lanjutan proyek Modul 5 (Breeze + Spatie Laravel Permission; role admin, petugas, siswa).
Fitur Modul 5 dipertahankan. NIM pada data uji dan payload: **250441100088**.

> Seluruh serangan hanya dilakukan pada proyek lokal sendiri.

## 1. Cara menjalankan

```bash
composer install
cp .env.example .env            # lalu isi DB_*; jalankan: php artisan key:generate
npm install && npm run build
php artisan migrate:fresh --seed
php artisan serve               # http://localhost:8000
php artisan test                # tes otomatis (RbacTest + KeamananTest)
```

* Password admin **tidak lagi `password`**. Nilainya dibaca dari `ADMIN_PASSWORD` di `.env`
  (bila kosong, seeder membuat password acak dan menampilkannya di konsol).
  Pada berkas `.env` di zip ini sudah terisi; untuk mengganti pada database yang sudah ada:
  `php artisan db:seed --class=UserSeeder`.
* Akun lain tetap `password`: `petugas@perpustakaan.test`, `siswa1@perpustakaan.test`, `siswa2@perpustakaan.test`.

## 2. Riwayat commit (satu atau lebih commit per nomor)

| Commit | Isi |
|--------|-----|
| `Modul 5: proyek dasar ...` | Kondisi awal (+ `.gitignore` yang diperbaiki agar `.env`, `vendor`, `node_modules` tidak ikut) |
| `Soal 1: ...` | Empat celah A1–A4 (kondisi rentan) |
| `Soal 2: ...` | Tambal A1, A2, A3 |
| `Soal 3: ...` | Gate `kelola-buku`, `can:` pada route, `@can` pada tampilan |
| `Soal 4: ...` | `PeminjamanPolicy` + `Gate::authorize` pada `show` |
| `Soal 5 (a) ...` (2 commit) | `APP_DEBUG=false`, route sementara pelempar `RuntimeException`, lalu dihapus |
| `Soal 5: (b)(c) ...` | Blokir `/.env`, ganti password admin seeder, pengujian, laporan |

Lihat kode sebelum/sesudah: `git show <commit>` atau `git diff <commit-lama> <commit-baru>`.
`jebakan.html` (Soal 1, A3) sengaja berada **di luar** folder proyek (satu tingkat di atas folder ini).

---

## 3. Soal 1 — Audit: empat celah (kondisi rentan)

**Source code (commit `Soal 1`)**

* **A1** `resources/views/components/buku-card.blade.php`: `<h3>{!! $judul !!}</h3>`
* **A2** `routes/web.php` → `Route::get('/buku-cari', [BukuController::class, 'cari'])` (di dalam grup `auth`);
  `BukuController@cari`:
  ```php
  $judul = $request->query('judul');
  $hasil = DB::select("SELECT * FROM bukus WHERE judul = '$judul'");
  return response()->json($hasil);
  ```
* **A3** `Route::get('/buku/{buku}/hapus', [Api\BukuController::class, 'destroy'])` (di dalam grup `auth`) + `jebakan.html`:
  ```html
  <a href="http://localhost:8000/buku/1/hapus">Klik di sini untuk klaim hadiah</a>
  ```
* **A4** `Route::get('/peminjaman/{id}', [PeminjamanController::class, 'show'])`;
  `show($id)` memakai `Peminjaman::with(...)->findOrFail($id)` lalu menampilkan view `peminjaman.show` tanpa pemeriksaan pemilik.

**Cara membuktikan (screenshot harus memperlihatkan URL + hasil)**

| Celah | Langkah | Hasil |
|-------|---------|-------|
| A1 | Login admin → *Kelola Buku* → tambah buku berjudul `<script>alert('250441100088')</script>` → buka `/buku` | Muncul popup berisi `250441100088` |
| A2 | Login → `http://localhost:8000/buku-cari?judul=' OR '1'='1` | JSON seluruh data buku |
| A3 | Login admin/petugas (tab 1) → buka `jebakan.html` dari file di tab lain → klik tautan | Buku id tersebut hilang dari daftar |
| A4 | Login `siswa1@perpustakaan.test` → buka `/peminjaman/{id milik siswa2}` | Status 200, data siswa lain tampil |

> Mencari id peminjaman siswa2: `php artisan tinker` → `App\Models\Peminjaman::with('anggota')->get(['id','anggota_id'])`.

**Penjelasan sebab & dampak**

* **A1 — XSS (Stored)**: `{!! !!}` mencetak string apa adanya sehingga tag `<script>` di judul dieksekusi browser. Judul tersimpan di database, jadi setiap pengunjung halaman daftar buku terkena. Dampak: pencurian sesi/aksi atas nama korban (termasuk admin), pengalihan ke situs palsu, pengubahan tampilan.
* **A2 — SQL Injection**: input pengguna digabung ke string SQL sehingga bisa mengubah struktur query (`' OR '1'='1` membuat kondisi selalu benar). Dampak: membaca seluruh data, dan dengan `UNION` dapat membaca tabel lain (mis. `users` beserta hash password), bahkan mengubah/menghapus data.
* **A3 — CSRF**: aksi yang mengubah data memakai method GET dan tanpa token CSRF. Browser korban otomatis mengirim cookie sesi ketika tautan di situs lain diklik, sehingga server mengira permintaan sah. Dampak: penghapusan data atas nama admin/petugas tanpa sepengetahuan mereka.
* **A4 — IDOR / Broken Access Control**: server hanya memeriksa "sudah login", bukan "apakah data ini milik pengguna". Dampak: siapa pun yang login bisa membaca data peminjaman (identitas, email, telepon) semua anggota dengan menebak/menambah angka `{id}`.

---

## 4. Soal 2 — Tambal A1, A2, A3

**Source code perbaikan (commit `Soal 2`)**

```blade
{{-- (a) buku-card.blade.php --}}
<h3>{{ $judul }}</h3>
```
```php
// (b) BukuController@cari
$judul = (string) $request->query('judul', '');
$hasil = DB::select('SELECT * FROM bukus WHERE judul = ?', [$judul]);
```
```php
// (c) routes/web.php — route GET /buku/{buku}/hapus DIHAPUS, diganti:
Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])->name('buku.destroy');
```
```blade
{{-- (c) form penghapusan (tombol Hapus di Kelola Buku & daftar buku) --}}
<form method="POST" action="{{ route('buku.destroy', $item->id) }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-kecil btn-hapus">Hapus</button>
</form>
```

**Hasil yang dicek (screenshot per sub-butir, tampilkan URL + status)**

* (a) buku berjudul payload tampil sebagai **teks** di `/buku`, tanpa popup.
* (b) `/buku-cari?judul=' OR '1'='1` → `[]`.
* (c) klik tautan `jebakan.html` → **404** dan buku tidak terhapus; tombol Hapus di aplikasi berhasil; Postman `DELETE http://localhost:8000/buku/{id}` tanpa token → **419 Page Expired**.

**Penjelasan**

* (a) `{{ }}` memanggil `e()` (htmlspecialchars): `<` menjadi `&lt;`, sehingga browser menampilkannya sebagai teks, bukan tag.
* (b) Dengan binding (`?`), nilai dikirim ke database terpisah dari teks SQL (prepared statement). Apa pun isinya hanya diperlakukan sebagai data; `' OR '1'='1` dicari sebagai judul literal sehingga tidak ada yang cocok.
* (c) Route GET dihapus, jadi tautan dari situs lain tidak punya target (404). Route DELETE hanya dapat dikirim lewat form yang membawa token CSRF (`@csrf`; `@method('DELETE')` mengubah POST menjadi DELETE). Situs penyerang tidak bisa membaca token itu, dan permintaan tanpa token (Postman) ditolak 419.

---

## 5. Soal 3 — Gate `kelola-buku`

**Source code**

```php
// app/Providers/AppServiceProvider.php (boot)
Gate::define('kelola-buku', fn (User $user): bool => $user->hasAnyRole(['admin', 'petugas']));
```
```php
// routes/web.php — menggantikan permission:kelola data buku
Route::middleware('can:kelola-buku')->group(function () {
    Route::get('/kelola/buku', [BukuController::class, 'kelola'])->name('buku.kelola');
    Route::get('/buku/create', [BukuController::class, 'create'])->name('buku.create');
    Route::post('/buku', [BukuController::class, 'store'])->name('buku.store');
    Route::get('/buku/{buku}/edit', [BukuController::class, 'edit'])->whereNumber('buku')->name('buku.edit');
    Route::put('/buku/{buku}', [BukuController::class, 'update'])->whereNumber('buku')->name('buku.update');
    Route::delete('/buku/{buku}', [BukuController::class, 'destroy'])->whereNumber('buku')->name('buku.destroy');
});
```
`routes/api.php`: grup `store/update/destroy` buku juga memakai `can:kelola-buku`.
Tampilan (`resources/views/buku/index.blade.php`):

```blade
@can('kelola-buku')
    <a href="{{ route('buku.create') }}" class="btn">Tambah buku</a>
@endcan
...
@can('kelola-buku')
    <a href="{{ route('buku.edit', $item->id) }}" class="btn btn-kecil">Ubah</a>
    <form method="POST" action="{{ route('buku.destroy', $item->id) }}"> @csrf @method('DELETE') ... Hapus ... </form>
@endcan
```

**Hasil yang dicek**: admin & petugas membuka `/buku/create` (200) dan dapat ubah/hapus; siswa membuka `/buku/create` → **403 Forbidden**, tombol Tambah/Ubah/Hapus tidak tampil di `/buku`.

**Penjelasan — Gate vs pemeriksaan role langsung pada route**

* Gate adalah **satu definisi aturan** yang dipakai di mana saja: middleware `can:`, controller (`Gate::authorize`), dan Blade (`@can`). Bila aturan berubah (mis. ditambah role "kepala perpustakaan"), cukup ubah satu tempat.
* Pemeriksaan role langsung (`role:admin|petugas`) menyebar di tiap route dan mudah tidak konsisten dengan tombol di tampilan (tombol tampil tetapi route menolak, atau sebaliknya).
* Gate bisa memuat logika, bukan hanya nama role, dan dapat menerima objek (dasar dari Policy di Soal 4). Pemeriksaan role statis tidak bisa.

---

## 6. Soal 4 — `PeminjamanPolicy`

**Source code**

```php
// app/Policies/PeminjamanPolicy.php
public function view(User $user, Peminjaman $peminjaman): bool
{
    if ($user->hasAnyRole(['admin', 'petugas'])) {
        return true;
    }

    $pemilik = $peminjaman->anggota?->user_id;

    return $pemilik !== null && (int) $pemilik === (int) $user->id;
}
```
```php
// PeminjamanController@show (setelah diperbaiki)
$peminjaman = Peminjaman::with(['anggota', 'buku.kategori', 'pemroses'])->findOrFail($id);
Gate::authorize('view', $peminjaman);
return view('peminjaman.show', ['peminjaman' => $peminjaman]);
```
Kebijakan didaftarkan eksplisit di `AppServiceProvider`: `Gate::policy(Peminjaman::class, PeminjamanPolicy::class);`

**Potongan yang dihapus/diganti dari Modul 5**: tidak ada. Pada Modul 5 tidak ada pemeriksaan kepemilikan manual untuk peminjaman; satu-satunya pemeriksaan kepemilikan (`/riwayat/{anggota}`) sudah memakai `AnggotaPolicy`. Jadi `show` peminjaman (celah A4 yang kita tulis di Soal 1) langsung ditutup dengan Policy.

**Hasil yang dicek (tiga akun)**: siswa pemilik → 200; siswa lain → **403**; petugas → 200 (admin juga 200).

**Penjelasan — mengapa pemeriksaan role saja tidak cukup untuk mencegah IDOR**
Role menjawab "siapa kamu" (siswa/petugas), sedangkan IDOR menyangkut "data mana yang boleh kamu buka". Semua siswa punya role yang sama dan sah mengakses fitur "lihat peminjaman", sehingga pemeriksaan role/permission akan meloloskan siswa mana pun untuk `{id}` apa pun. Harus ada pemeriksaan **per objek**: apakah baris data ini milik pengguna tersebut. Itulah yang dilakukan Policy.

---

## 7. Soal 5 — Penguatan konfigurasi

**Potongan `.env` (tanpa `APP_KEY` dan password basis data)**

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=perpustakaan_250441100088
DB_USERNAME=root

ADMIN_PASSWORD=<password kuat, tidak ditampilkan>
```

**Perubahan seeder** (`database/seeders/UserSeeder.php`): admin memakai `config('app.admin_password')` (dari `ADMIN_PASSWORD`), atau password acak bila kosong:

```php
$passwordAdmin = config('app.admin_password');
if (blank($passwordAdmin)) {
    $passwordAdmin = Str::password(20, symbols: false);
    $this->command?->warn("ADMIN_PASSWORD kosong. Password admin acak: {$passwordAdmin}");
}
// ['Admin Perpustakaan', 'admin@perpustakaan.test', 'admin', $passwordAdmin],
```

**Pengamanan `/.env`**: `.env` tercantum di `.gitignore` (+ `.env.example` disertakan). Document root harus `public/` (sudah demikian pada `php artisan serve`; pada Laragon/Apache arahkan ke folder `public`).
Sebagai lapisan cadangan ditambahkan aturan `<FilesMatch "^\.">  Require all denied` pada `public/.htaccess` dan `.htaccess` di root proyek (efektif di Apache; Nginx perlu `location ~ /\. { deny all; }`).

**Langkah screenshot**

* (a) Dengan `APP_DEBUG=false`, buat route sementara `Route::get('/uji-error', fn () => throw new \RuntimeException('uji'));`, buka `http://localhost:8000/uji-error` → halaman "Server Error" tanpa jejak kode, lalu hapus route (urutan ini sudah tercermin pada dua commit `Soal 5 (a)`).
* (b) Buka `http://localhost:8000/.env` → 404 (atau 403).
* (c) Login `admin@perpustakaan.test` dengan `password` → ditolak; dengan password baru → berhasil.

**Penjelasan risiko**

* `APP_DEBUG=true` di lingkungan yang dapat diakses orang lain menampilkan stack trace, path server, isi query SQL, versi framework/library, bahkan nilai konfigurasi. Itu memberi peta serangan kepada penyerang.
* `.env` yang bisa diunduh membocorkan `APP_KEY` (memungkinkan pemalsuan/dekripsi cookie & data terenkripsi), kredensial database, kredensial email, dan kunci API lain: akibatnya pengambilalihan sistem total.
* Password bawaan (`password`) adalah kredensial yang pertama kali dicoba penyerang (credential guessing/stuffing); akun admin yang tertembus berarti akses penuh ke seluruh data dan pengguna.

### Tabel pengujian (isi kolom *Hasil Aktual* dari uji manual di browser/Postman Anda)

| No | Role | Skenario | Hasil yang Diharapkan | Hasil Aktual |
|----|------|----------|-----------------------|--------------|
| 1 | Belum login | Membuka `/peminjaman/1` | Dialihkan ke halaman login | |
| 2 | Admin | Menambah buku berjudul `<script>alert(1)</script>` | Judul tampil sebagai teks, tanpa popup | |
| 3 | Admin | Membuka `/buku-cari?judul=' OR '1'='1` | Hasil kosong `[]` | |
| 4 | Admin | Mengirim `DELETE /buku/{id}` via Postman tanpa token | 419 Page Expired | |
| 5 | Admin | Menghapus buku lewat tombol Hapus | Buku terhapus | |
| 6 | Admin | Membuka tautan `jebakan.html` saat login | Buku tidak terhapus (404/405) | |
| 7 | Siswa | Membuka `/buku/create` langsung pada URL | 403 Forbidden, tombol kelola tidak tampil | |
| 8 | Siswa | Membuka peminjaman milik sendiri | 200, data tampil | |
| 9 | Siswa | Membuka peminjaman milik siswa lain | 403 Forbidden | |
| 10 | Petugas | Membuka peminjaman milik siswa mana pun | 200, data tampil | |
| 11 | Semua | Membuka halaman error dengan `APP_DEBUG=false` | Pesan error umum tanpa jejak kode | |
| 12 | Semua | Membuka `/.env` dan login admin dengan `password` | 404 atau 403; login ditolak | |

Tes otomatis yang memeriksa perilaku di atas ada di `tests/Feature/KeamananTest.php` (`php artisan test`).
