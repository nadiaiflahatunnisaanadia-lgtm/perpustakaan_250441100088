<?php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    /**
     * SOAL 4: siapa yang boleh melihat satu data peminjaman.
     *  - admin & petugas : semua peminjaman
     *  - siswa           : hanya peminjaman miliknya sendiri (peminjaman -> anggota -> user_id)
     *
     * Pemeriksaan dilakukan terhadap OBJEK (baris data), bukan sekadar role,
     * sehingga siswa tidak bisa membuka peminjaman siswa lain dengan mengganti {id} di URL (IDOR).
     */
    public function view(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->hasAnyRole(['admin', 'petugas'])) {
            return true;
        }

        $pemilik = $peminjaman->anggota?->user_id;

        return $pemilik !== null && (int) $pemilik === (int) $user->id;
    }
}
