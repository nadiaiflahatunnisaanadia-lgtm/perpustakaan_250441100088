<?php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    public function view(User $user, Peminjaman $peminjaman): bool
    {
        if ($user->hasAnyRole(['admin', 'petugas'])) {
            return true;
        }

        $pemilik = $peminjaman->anggota?->user_id;

        return $pemilik !== null && (int) $pemilik === (int) $user->id;
    }
}
