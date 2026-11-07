<?php

namespace App\Policies;

use App\Models\Anggota;
use App\Models\User;

class AnggotaPolicy
{

    public function lihatRiwayat(User $user, Anggota $anggota): bool
    {
        if ($anggota->user_id !== null && $anggota->user_id === $user->id) {
            return $user->can('melihat riwayat peminjaman sendiri');
        }

        return $user->can('kelola peminjaman');
    }
}
