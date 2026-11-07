<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alur peminjaman: diajukan -> dipinjam -> dikembalikan (atau ditolak).
     */
    public function up(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->string('status', 20)->default('diajukan')->after('tanggal_kembali');
            $table->foreignId('diproses_oleh')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
        });

        // Data lama (Modul 1-4): sudah ada tanggal_kembali = dikembalikan, selain itu = dipinjam.
        DB::table('peminjamans')->whereNotNull('tanggal_kembali')->update(['status' => 'dikembalikan']);
        DB::table('peminjamans')->whereNull('tanggal_kembali')->update(['status' => 'dipinjam']);
    }

    public function down(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diproses_oleh');
            $table->dropColumn('status');
        });
    }
};
