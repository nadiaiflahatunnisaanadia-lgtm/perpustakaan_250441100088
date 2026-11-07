<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
