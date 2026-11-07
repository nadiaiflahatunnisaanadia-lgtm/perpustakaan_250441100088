<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Modul 6 — Pengujian perbaikan keamanan (Soal 2 s.d. Soal 5).
 */
class KeamananTest extends TestCase
{
    use RefreshDatabase;

    private const NIM = '250441100088';

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

    private function buatBuku(string $judul = 'Buku Uji'): Buku
    {
        $kategori = Kategori::firstOrCreate(['nama' => 'Novel']);

        return Buku::create([
            'kategori_id'  => $kategori->id,
            'judul'        => $judul,
            'penulis'      => 'Penulis Uji',
            'tahun_terbit' => 2020,
        ]);
    }

    // ------------------------------------------------------------------ Soal 2 (a) — XSS

    public function test_soal2a_judul_berpayload_tampil_sebagai_teks_bukan_script(): void
    {
        $payload = "<script>alert('" . self::NIM . "')</script>";
        $this->buatBuku($payload);

        $this->actingAs($this->admin)->get('/buku')
            ->assertOk()
            ->assertDontSee($payload, false)
            ->assertSee(e($payload), false);
    }

    // ------------------------------------------------------------------ Soal 2 (b) — SQL Injection

    public function test_soal2b_payload_sql_injection_menghasilkan_array_kosong(): void
    {
        $this->buatBuku('Buku Satu');
        $this->buatBuku('Buku Dua');

        $respons = $this->actingAs($this->admin)
            ->getJson('/buku-cari?' . http_build_query(['judul' => "' OR '1'='1"]));

        $respons->assertOk();
        $this->assertSame([], $respons->json());
    }

    public function test_soal2b_pencarian_judul_yang_sah_tetap_bekerja(): void
    {
        $this->buatBuku('Buku Satu');
        $this->buatBuku('Buku Dua');

        $this->actingAs($this->admin)
            ->getJson('/buku-cari?' . http_build_query(['judul' => 'Buku Satu']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.judul', 'Buku Satu');
    }

    public function test_soal2b_buku_cari_wajib_login(): void
    {
        $this->getJson('/buku-cari?judul=x')->assertUnauthorized();
    }

    // ------------------------------------------------------------------ Soal 2 (c) — CSRF

    public function test_soal2c_tautan_get_lama_tidak_lagi_menghapus_buku(): void
    {
        $buku = $this->buatBuku('Buku Jebakan');

        $this->actingAs($this->admin)->get("/buku/{$buku->id}/hapus")->assertNotFound();

        $this->assertDatabaseHas('bukus', ['id' => $buku->id]);
    }

    public function test_soal2c_delete_tanpa_token_csrf_ditolak_419(): void
    {
        $buku = $this->buatBuku();

        // Pemeriksaan CSRF dinonaktifkan Laravel saat env = testing; kita aktifkan agar sama dengan aplikasi asli.
        $this->app['env'] = 'local';

        $this->actingAs($this->admin)->delete("/buku/{$buku->id}")->assertStatus(419);

        $this->assertDatabaseHas('bukus', ['id' => $buku->id]);
    }

    public function test_soal2c_delete_dengan_token_csrf_berhasil_menghapus(): void
    {
        $buku = $this->buatBuku();

        $this->app['env'] = 'local';

        $this->actingAs($this->admin)
            ->withSession(['_token' => 'token-uji'])
            ->delete("/buku/{$buku->id}", ['_token' => 'token-uji'])
            ->assertRedirect();

        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }

    public function test_soal2c_tombol_hapus_memakai_form_dengan_csrf_dan_method_delete(): void
    {
        $this->actingAs($this->admin)->get('/kelola/buku')
            ->assertOk()
            ->assertSee('id="form-hapus"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_soal2c_siswa_tidak_boleh_menghapus_buku(): void
    {
        $buku = $this->buatBuku();

        $this->actingAs($this->siswa)->delete("/buku/{$buku->id}")->assertForbidden();

        $this->assertDatabaseHas('bukus', ['id' => $buku->id]);
    }

    // ------------------------------------------------------------------ Soal 3 — Gate kelola-buku

    public function test_soal3_gate_kelola_buku_hanya_untuk_admin_dan_petugas(): void
    {
        $this->assertTrue(Gate::forUser($this->admin)->allows('kelola-buku'));
        $this->assertTrue(Gate::forUser($this->petugas)->allows('kelola-buku'));
        $this->assertFalse(Gate::forUser($this->siswa)->allows('kelola-buku'));
    }

    public function test_soal3_admin_dan_petugas_membuka_form_tambah_buku(): void
    {
        $this->actingAs($this->admin)->get('/buku/create')->assertOk();
        $this->actingAs($this->petugas)->get('/buku/create')->assertOk();
    }

    public function test_soal3_siswa_membuka_buku_create_langsung_menerima_403(): void
    {
        $this->actingAs($this->siswa)->get('/buku/create')->assertForbidden();
    }

    public function test_soal3_belum_login_dialihkan_ke_login(): void
    {
        $this->get('/buku/create')->assertRedirect('/login');
    }

    public function test_soal3_siswa_ditolak_pada_semua_route_pengelolaan_buku(): void
    {
        $buku = $this->buatBuku();
        $kategori = Kategori::firstOrFail();
        $data = ['kategori_id' => $kategori->id, 'judul' => 'Judul Ilegal', 'penulis' => 'Penulis', 'tahun_terbit' => 2021];

        $this->actingAs($this->siswa);
        $this->get('/kelola/buku')->assertForbidden();
        $this->get("/buku/{$buku->id}/edit")->assertForbidden();
        $this->post('/buku', $data)->assertForbidden();
        $this->put("/buku/{$buku->id}", $data)->assertForbidden();
        $this->delete("/buku/{$buku->id}")->assertForbidden();
        $this->postJson('/api/buku', $data)->assertForbidden();
        $this->putJson("/api/buku/{$buku->id}", $data)->assertForbidden();
        $this->deleteJson("/api/buku/{$buku->id}")->assertForbidden();

        $this->assertDatabaseHas('bukus', ['id' => $buku->id, 'judul' => $buku->judul]);
        $this->assertDatabaseMissing('bukus', ['judul' => 'Judul Ilegal']);
    }

    public function test_soal3_admin_dan_petugas_dapat_tambah_ubah_dan_hapus_buku(): void
    {
        $kategori = Kategori::create(['nama' => 'Novel']);

        foreach ([$this->admin, $this->petugas] as $pengelola) {
            $judul = 'Buku dari ' . $pengelola->id;

            $this->actingAs($pengelola)
                ->post('/buku', ['kategori_id' => $kategori->id, 'judul' => $judul, 'penulis' => 'Penulis', 'tahun_terbit' => 2022])
                ->assertRedirect(route('buku.index'));

            $buku = Buku::where('judul', $judul)->firstOrFail();

            $this->get("/buku/{$buku->id}/edit")->assertOk()->assertSee($judul);

            $this->put("/buku/{$buku->id}", ['kategori_id' => $kategori->id, 'judul' => $judul . ' (ubah)', 'penulis' => 'Penulis', 'tahun_terbit' => 2023])
                ->assertRedirect(route('buku.index'));
            $this->assertDatabaseHas('bukus', ['id' => $buku->id, 'judul' => $judul . ' (ubah)']);

            $this->delete("/buku/{$buku->id}")->assertRedirect();
            $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
        }
    }

    public function test_soal3_tombol_kelola_tampil_untuk_petugas_dan_admin(): void
    {
        $buku = $this->buatBuku();

        foreach ([$this->petugas, $this->admin] as $pengelola) {
            $this->actingAs($pengelola)->get('/buku')
                ->assertOk()
                ->assertSee('Tambah buku')
                ->assertSee("/buku/{$buku->id}/edit", false)
                ->assertSee('name="_method" value="DELETE"', false);
        }
    }

    public function test_soal3_tombol_kelola_tidak_tampil_untuk_siswa(): void
    {
        $this->buatBuku();

        $this->actingAs($this->siswa)->get('/buku')
            ->assertOk()
            ->assertDontSee('Tambah buku')
            ->assertDontSee('/buku/create', false)
            ->assertDontSee('/edit', false)
            ->assertDontSee('name="_method" value="DELETE"', false);
    }

    // ------------------------------------------------------------------ Soal 4 — Policy (IDOR)

    private function buatPeminjaman(User $pemilik, Buku $buku): Peminjaman
    {
        return Peminjaman::create([
            'anggota_id'     => $pemilik->anggota->id,
            'buku_id'        => $buku->id,
            'tanggal_pinjam' => now()->toDateString(),
            'status'         => Peminjaman::DIPINJAM,
        ]);
    }

    public function test_soal4_belum_login_dialihkan_ke_login(): void
    {
        $this->get('/peminjaman/1')->assertRedirect('/login');
    }

    public function test_soal4_siswa_membuka_peminjaman_miliknya_menerima_200(): void
    {
        $buku = $this->buatBuku('Buku Milik Siswa');
        $pinjam = $this->buatPeminjaman($this->siswa, $buku);

        $this->actingAs($this->siswa)->get("/peminjaman/{$pinjam->id}")
            ->assertOk()
            ->assertSee('Buku Milik Siswa');
    }

    public function test_soal4_siswa_membuka_peminjaman_siswa_lain_menerima_403(): void
    {
        $buku = $this->buatBuku('Buku Rahasia Siswa Lain');
        $pinjam = $this->buatPeminjaman($this->siswaLain, $buku);

        $this->actingAs($this->siswa)->get("/peminjaman/{$pinjam->id}")
            ->assertForbidden()
            ->assertDontSee('Buku Rahasia Siswa Lain');
    }

    public function test_soal4_petugas_dan_admin_membuka_peminjaman_siapa_pun_menerima_200(): void
    {
        $pinjam = $this->buatPeminjaman($this->siswaLain, $this->buatBuku('Buku Siswa Lain'));

        $this->actingAs($this->petugas)->get("/peminjaman/{$pinjam->id}")->assertOk()->assertSee('Buku Siswa Lain');
        $this->actingAs($this->admin)->get("/peminjaman/{$pinjam->id}")->assertOk()->assertSee('Buku Siswa Lain');
    }

    public function test_soal4_peminjaman_yang_tidak_ada_menerima_404(): void
    {
        $this->actingAs($this->petugas)->get('/peminjaman/999999')->assertNotFound();
    }
}
