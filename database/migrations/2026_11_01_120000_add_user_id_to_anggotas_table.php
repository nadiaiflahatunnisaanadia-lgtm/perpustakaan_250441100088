<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menghubungkan akun login (users) dengan data anggota perpustakaan.
     * Relasi inilah yang dipakai untuk membatasi riwayat peminjaman
     * agar setiap pengguna hanya melihat datanya sendiri.
     */
    public function up(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();

            // satu akun = satu data anggota
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('anggotas', function (Blueprint $table) {
            // urutan penting: foreign key -> unique -> kolom (berlaku untuk MySQL dan SQLite)
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
