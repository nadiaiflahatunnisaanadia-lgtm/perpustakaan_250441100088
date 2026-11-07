<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private User $siswa;
    private User $siswaLain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin     = $this->buatUser('admin');
        $this->petugas   = $this->buatUser('petugas');
        $this->siswa     = $this->buatUser('siswa');
        $this->siswaLain = $this->buatUser('siswa');
    }

    private function buatUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        Anggota::create(['user_id' => $user->id, 'nama' => $user->name, 'email' => $user->email, 'no_telepon' => '0812']);

        return $user;
    }

    private function buatBuku(): Buku
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Novel']);

        return Buku::create(['kategori_id' => $kategori->id, 'judul' => 'Buku Uji ' . uniqid(), 'penulis' => 'Penulis Uji', 'tahun_terbit' => 2020]);
    }


    public function test_registrasi_membuat_siswa_dan_password_di_hash(): void
    {
        $this->post('/register', [
            'name' => 'Murid Baru', 'email' => 'baru@contoh.test', 'no_telepon' => '0899',
            'password' => 'rahasia-123', 'password_confirmation' => 'rahasia-123',
        ])->assertRedirect(route('buku.index', absolute: false));

        $user = User::where('email', 'baru@contoh.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame('rahasia-123', $user->password);
        $this->assertTrue(Hash::check('rahasia-123', $user->password));
        $this->assertTrue($user->hasRole('siswa'));
        $this->assertSame($user->id, Anggota::where('email', 'baru@contoh.test')->value('user_id'));
    }

    public function test_registrasi_tidak_bisa_memilih_role(): void
    {
        $this->post('/register', [
            'name' => 'Penyusup', 'email' => 'penyusup@contoh.test', 'no_telepon' => '0899',
            'password' => 'rahasia-123', 'password_confirmation' => 'rahasia-123',
            'role' => 'admin', 'roles' => ['admin'],
        ]);

        $user = User::where('email', 'penyusup@contoh.test')->firstOrFail();

        $this->assertTrue($user->hasRole('siswa'));
        $this->assertFalse($user->hasRole('admin'));
        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_login_valid_dan_tidak_valid(): void
    {
        $this->post('/login', ['email' => $this->siswa->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $this->siswa->email, 'password' => 'password'])
            ->assertRedirect(route('buku.index', absolute: false));
        $this->assertAuthenticatedAs($this->siswa);
    }

    public function test_login_diarahkan_sesuai_role(): void
    {
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard', absolute: false));
        $this->post('/logout');

        $this->post('/login', ['email' => $this->petugas->email, 'password' => 'password'])
            ->assertRedirect(route('petugas.dashboard', absolute: false));
    }

    public function test_guest_middleware_menolak_user_login_ke_halaman_login_dan_register(): void
    {
        $this->actingAs($this->admin)->get('/login')->assertRedirect(route('admin.dashboard', absolute: false));
        $this->actingAs($this->siswa)->get('/register')->assertRedirect(route('buku.index', absolute: false));
    }

    // ------------------------------------------------------------------ Soal 5 (tabel pengujian)

    public function test_1_belum_login_dashboard_admin_dialihkan_ke_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/buku')->assertRedirect('/login');
        $this->get('/profil')->assertRedirect('/login');
    }

    public function test_2_admin_membuka_dashboard_admin(): void
    {
        $this->actingAs($this->admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_3_admin_menambah_mengubah_menghapus_buku(): void
    {
        $this->actingAs($this->admin)->get('/kelola/buku')->assertOk();

        $kategori = Kategori::create(['nama' => 'Novel']);
        $data = ['kategori_id' => $kategori->id, 'judul' => 'Judul Baru', 'penulis' => 'Penulis A', 'tahun_terbit' => 2021];

        $id = $this->actingAs($this->admin)->postJson('/api/buku', $data)->assertCreated()->json('data.id');
        $this->putJson("/api/buku/{$id}", ['judul' => 'Judul Diubah'])->assertOk();
        $this->assertDatabaseHas('bukus', ['id' => $id, 'judul' => 'Judul Diubah']);
        $this->deleteJson("/api/buku/{$id}")->assertOk();
        $this->assertDatabaseMissing('bukus', ['id' => $id]);
    }

    public function test_4_admin_mengelola_akun_dan_role(): void
    {
        $this->actingAs($this->admin)->get('/admin/users')->assertOk();

        $this->post('/admin/users', [
            'name' => 'Staf Baru', 'email' => 'staf@contoh.test', 'no_telepon' => '0811',
            'role' => 'petugas', 'password' => 'rahasia-123', 'password_confirmation' => 'rahasia-123',
        ])->assertRedirect(route('admin.users.index'));

        $staf = User::where('email', 'staf@contoh.test')->firstOrFail();
        $this->assertTrue($staf->hasRole('petugas'));

        $this->put("/admin/users/{$staf->id}", ['name' => 'Staf Baru', 'email' => 'staf@contoh.test', 'role' => 'siswa'])
            ->assertRedirect(route('admin.users.index'));
        $this->assertTrue($staf->fresh()->hasRole('siswa'));
        $this->assertFalse($staf->fresh()->hasRole('petugas'));

        $this->delete("/admin/users/{$staf->id}")->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $staf->id]);
    }

    public function test_admin_tidak_bisa_hapus_diri_sendiri_atau_ubah_role_admin_terakhir(): void
    {
        $this->actingAs($this->admin)->delete("/admin/users/{$this->admin->id}")->assertSessionHas('gagal');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        $this->put("/admin/users/{$this->admin->id}", ['name' => 'A', 'email' => $this->admin->email, 'role' => 'siswa'])
            ->assertSessionHas('gagal');
        $this->assertTrue($this->admin->fresh()->hasRole('admin'));
    }

    public function test_role_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'X', 'email' => 'x@contoh.test', 'role' => 'superuser',
            'password' => 'rahasia-123', 'password_confirmation' => 'rahasia-123',
        ])->assertSessionHasErrors('role');
    }

    public function test_5_admin_mengakses_peminjaman_pengembalian_dan_laporan(): void
    {
        $this->actingAs($this->admin);
        $this->get('/kelola/peminjaman')->assertOk();
        $this->get('/kelola/pengembalian')->assertOk();
        $this->get('/laporan')->assertOk();
        $this->getJson('/api/rekap/buku-per-kategori')->assertOk();
    }

    public function test_6_petugas_membuka_dashboard_petugas(): void
    {
        $this->actingAs($this->petugas)->get('/petugas/dashboard')->assertOk();
        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_7_petugas_mengelola_buku(): void
    {
        $kategori = Kategori::create(['nama' => 'Novel']);
        $this->actingAs($this->petugas)->get('/kelola/buku')->assertOk();
        $this->postJson('/api/buku', ['kategori_id' => $kategori->id, 'judul' => 'Dari Petugas', 'penulis' => 'Pen', 'tahun_terbit' => 2020])
            ->assertCreated();
    }

    public function test_8_petugas_memproses_peminjaman_dan_pengembalian(): void
    {
        $buku = $this->buatBuku();

        // siswa mengajukan
        $this->actingAs($this->siswa)->post("/buku/{$buku->id}/ajukan")->assertSessionHas('sukses');
        $pinjam = Peminjaman::firstOrFail();
        $this->assertSame('diajukan', $pinjam->status);
        $this->assertSame($this->siswa->anggota->id, $pinjam->anggota_id);

        // petugas menyetujui lalu mencatat pengembalian
        $this->actingAs($this->petugas)->patch("/kelola/peminjaman/{$pinjam->id}/setujui")->assertSessionHas('sukses');
        $this->assertSame('dipinjam', $pinjam->fresh()->status);
        $this->assertSame($this->petugas->id, $pinjam->fresh()->diproses_oleh);

        $this->patch("/kelola/pengembalian/{$pinjam->id}")->assertSessionHas('sukses');
        $this->assertSame('dikembalikan', $pinjam->fresh()->status);
        $this->assertNotNull($pinjam->fresh()->tanggal_kembali);
    }

    public function test_petugas_bisa_menolak_pengajuan_dan_mencatat_peminjaman_langsung(): void
    {
        $buku = $this->buatBuku();
        $this->actingAs($this->siswa)->post("/buku/{$buku->id}/ajukan");
        $pinjam = Peminjaman::firstOrFail();

        $this->actingAs($this->petugas)->patch("/kelola/peminjaman/{$pinjam->id}/tolak");
        $this->assertSame('ditolak', $pinjam->fresh()->status);

        $this->post('/kelola/peminjaman', ['anggota_id' => $this->siswa->anggota->id, 'buku_id' => $buku->id])
            ->assertRedirect(route('peminjaman.index'));
        $this->assertDatabaseHas('peminjamans', ['status' => 'dipinjam', 'buku_id' => $buku->id]);
    }

    public function test_9_petugas_tidak_bisa_mengelola_akun_atau_role(): void
    {
        $this->actingAs($this->petugas);
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/users/tambah')->assertForbidden();
        $this->post('/admin/users', ['name' => 'X', 'email' => 'x@y.test', 'role' => 'admin', 'password' => 'abcdefgh1', 'password_confirmation' => 'abcdefgh1'])
            ->assertForbidden();
        $this->put("/admin/users/{$this->siswa->id}", ['name' => 'X', 'email' => 'x@y.test', 'role' => 'admin'])->assertForbidden();
        $this->delete("/admin/users/{$this->siswa->id}")->assertForbidden();

        $this->assertFalse($this->siswa->fresh()->hasRole('admin'));
    }

    public function test_10_siswa_melihat_daftar_dan_detail_buku(): void
    {
        $buku = $this->buatBuku();

        $this->actingAs($this->siswa)->get('/buku')->assertOk()->assertSee($buku->judul);
        $this->get("/buku/{$buku->id}")->assertOk()->assertSee($buku->judul);
        $this->getJson('/api/buku')->assertOk();
    }

    public function test_11_siswa_melihat_dan_mengubah_profil_sendiri(): void
    {
        $this->actingAs($this->siswa)->get('/profil')->assertOk()->assertSee($this->siswa->email);

        $this->patch('/profil', ['name' => 'Nama Baru', 'email' => $this->siswa->email, 'no_telepon' => '0877'])
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('Nama Baru', $this->siswa->fresh()->name);
        $this->assertSame('0877', $this->siswa->anggota->fresh()->no_telepon);
    }

    public function test_profil_tidak_bisa_dipakai_mengubah_user_lain(): void
    {
        // tidak ada parameter id pada route; id yang dikirim lewat body diabaikan
        $this->actingAs($this->siswa)->patch('/profil', [
            'id' => $this->siswaLain->id, 'user_id' => $this->siswaLain->id,
            'name' => 'Diubah', 'email' => $this->siswa->email,
        ]);

        $this->assertSame('Diubah', $this->siswa->fresh()->name);
        $this->assertNotSame('Diubah', $this->siswaLain->fresh()->name);
        $this->get("/profil/{$this->siswaLain->id}")->assertNotFound();
    }

    public function test_siswa_tidak_bisa_memberi_dirinya_role_lewat_profil(): void
    {
        $this->actingAs($this->siswa)->patch('/profil', [
            'name' => 'S', 'email' => $this->siswa->email, 'role' => 'admin', 'roles' => ['admin'],
        ]);

        $this->assertFalse($this->siswa->fresh()->hasRole('admin'));
        $this->assertTrue($this->siswa->fresh()->hasRole('siswa'));
    }

    public function test_12_siswa_melihat_riwayat_sendiri(): void
    {
        $buku = $this->buatBuku();
        Peminjaman::create([
            'anggota_id' => $this->siswa->anggota->id, 'buku_id' => $buku->id,
            'tanggal_pinjam' => now()->subDays(10), 'tanggal_kembali' => now()->subDays(2), 'status' => 'dikembalikan',
        ]);

        $this->actingAs($this->siswa)->get('/peminjaman-saya')->assertOk()->assertSee($buku->judul);
        $this->get("/riwayat/{$this->siswa->anggota->id}")->assertOk()->assertSee($buku->judul);
    }

    public function test_13_siswa_ditolak_ke_dashboard_dan_pengelolaan(): void
    {
        $this->actingAs($this->siswa);
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/petugas/dashboard')->assertForbidden();
        $this->get('/kelola/buku')->assertForbidden();
        $this->get('/kelola/peminjaman')->assertForbidden();
        $this->get('/kelola/pengembalian')->assertForbidden();
        $this->get('/laporan')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();

        $kategori = Kategori::create(['nama' => 'Novel']);
        $this->postJson('/api/buku', ['kategori_id' => $kategori->id, 'judul' => 'Ilegal', 'penulis' => 'Pen', 'tahun_terbit' => 2020])
            ->assertForbidden();
        $this->getJson('/api/rekap/peminjaman-per-buku')->assertForbidden();
        $this->assertDatabaseMissing('bukus', ['judul' => 'Ilegal']);
    }

    public function test_14_siswa_tidak_bisa_melihat_riwayat_siswa_lain(): void
    {
        $buku = $this->buatBuku();
        Peminjaman::create([
            'anggota_id' => $this->siswaLain->anggota->id, 'buku_id' => $buku->id,
            'tanggal_pinjam' => now(), 'status' => 'dipinjam',
        ]);

        $this->actingAs($this->siswa)->get("/riwayat/{$this->siswaLain->anggota->id}")->assertForbidden();

        // halaman "peminjaman saya" hanya memuat data sendiri
        $this->get('/peminjaman-saya')->assertOk()->assertDontSee($buku->judul);
    }

    public function test_staf_boleh_melihat_riwayat_anggota(): void
    {
        $this->actingAs($this->petugas)->get("/riwayat/{$this->siswa->anggota->id}")->assertOk();
        $this->actingAs($this->admin)->get("/riwayat/{$this->siswaLain->anggota->id}")->assertOk();
    }

    public function test_15_logout_lalu_halaman_dilindungi_butuh_login(): void
    {
        foreach ([$this->admin, $this->petugas, $this->siswa] as $user) {
            $this->actingAs($user)->post('/logout')->assertRedirect('/');
            $this->assertGuest();
            $this->get('/buku')->assertRedirect('/login');
            $this->get('/profil')->assertRedirect('/login');
        }
    }

  

    public function test_api_tanpa_login_ditolak_401(): void
    {
        $this->getJson('/api/buku')->assertUnauthorized();
        $this->postJson('/api/buku', [])->assertUnauthorized();
        $this->deleteJson('/api/buku/1')->assertUnauthorized();
    }

    public function test_api_show_buku_menyembunyikan_peminjaman_dari_siswa(): void
    {
        $buku = $this->buatBuku();
        Peminjaman::create(['anggota_id' => $this->siswaLain->anggota->id, 'buku_id' => $buku->id, 'tanggal_pinjam' => now(), 'status' => 'dipinjam']);

        $this->actingAs($this->siswa)->getJson("/api/buku/{$buku->id}")->assertOk()->assertJsonMissingPath('data.peminjamans');
        $this->actingAs($this->petugas)->getJson("/api/buku/{$buku->id}")->assertOk()->assertJsonPath('data.peminjamans.0.status', 'dipinjam');
    }

    public function test_tidak_bisa_mengajukan_buku_yang_sama_dua_kali(): void
    {
        $buku = $this->buatBuku();
        $this->actingAs($this->siswa)->post("/buku/{$buku->id}/ajukan");
        $this->post("/buku/{$buku->id}/ajukan")->assertSessionHas('gagal');

        $this->assertSame(1, Peminjaman::count());
    }

    public function test_siswa_tidak_bisa_memproses_peminjaman(): void
    {
        $buku = $this->buatBuku();
        $this->actingAs($this->siswa)->post("/buku/{$buku->id}/ajukan");
        $pinjam = Peminjaman::firstOrFail();

        $this->patch("/kelola/peminjaman/{$pinjam->id}/setujui")->assertForbidden();
        $this->patch("/kelola/pengembalian/{$pinjam->id}")->assertForbidden();
        $this->assertSame('diajukan', $pinjam->fresh()->status);
    }

    public function test_admin_dan_petugas_juga_bisa_mengajukan_dan_melihat_riwayat_sendiri(): void
    {
        $buku = $this->buatBuku();
        $admin = User::factory()->create();
        $admin->assignRole('admin'); // tanpa data anggota: dibuat otomatis

        $this->actingAs($admin)->post("/buku/{$buku->id}/ajukan")->assertSessionHas('sukses');
        $this->assertNotNull($admin->fresh()->anggota);
        $this->get('/peminjaman-saya')->assertOk()->assertSee($buku->judul);
    }

    public function test_matriks_permission_sesuai_tabel_soal(): void
    {
        $this->assertSame(16, Permission::count());
        $this->assertSame(3, Role::count());

        $harapan = [
            'akses dashboard admin'              => [1, 0, 0],
            'akses dashboard petugas'            => [0, 1, 0],
            'lihat daftar buku'                  => [1, 1, 1],
            'kelola data buku'                   => [1, 1, 0],
            'kelola data pengguna'               => [1, 0, 0],
            'kelola role pengguna'               => [1, 0, 0],
            'kelola peminjaman'                  => [1, 1, 0],
            'kelola pengembalian'                => [1, 1, 0],
            'mengajukan peminjaman buku'         => [1, 1, 1],
            'memproses persetujuan peminjaman'   => [1, 1, 0],
            'mencatat pengembalian buku'         => [1, 1, 0],
            'melihat status peminjaman sendiri'  => [1, 1, 1],
            'melihat riwayat peminjaman sendiri' => [1, 1, 1],
            'lihat laporan perpustakaan'         => [1, 1, 0],
            'lihat profil sendiri'               => [1, 1, 1],
            'ubah profil sendiri'                => [1, 1, 1],
        ];

        foreach ($harapan as $permission => [$a, $p, $s]) {
            $this->assertSame((bool) $a, Role::findByName('admin')->hasPermissionTo($permission), "admin: $permission");
            $this->assertSame((bool) $p, Role::findByName('petugas')->hasPermissionTo($permission), "petugas: $permission");
            $this->assertSame((bool) $s, Role::findByName('siswa')->hasPermissionTo($permission), "siswa: $permission");
        }
    }

    public function test_menu_blade_sesuai_role(): void
    {
        $this->actingAs($this->siswa)->get('/buku')
            ->assertDontSee('Dashboard Admin')->assertDontSee('Kelola Buku')->assertDontSee('Pengguna')
            ->assertSee('Peminjaman Saya');

        $this->actingAs($this->petugas)->get('/buku')
            ->assertSee('Dashboard Petugas')->assertSee('Kelola Buku')->assertDontSee('Dashboard Admin');

        $this->actingAs($this->admin)->get('/buku')
            ->assertSee('Dashboard Admin')->assertSee('Pengguna')->assertDontSee('Dashboard Petugas');
    }
}
