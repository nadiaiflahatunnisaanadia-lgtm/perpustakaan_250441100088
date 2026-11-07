<?php

namespace App\Models;


use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function anggota(): HasOne
    {
        return $this->hasOne(Anggota::class);
    }

    public function anggotaAtauBuat(?string $noTelepon = null): Anggota
    {
        return $this->anggota
            ?? tap(
                Anggota::create([
                    'user_id'    => $this->id,
                    'nama'       => $this->name,
                    'email'      => $this->email,
                    'no_telepon' => $noTelepon ?? '-',
                ]),
                fn ($anggota) => $this->setRelation('anggota', $anggota)
            );
    }


    public function rutePendaratan(): string
    {
        return match (true) {
            $this->can('akses dashboard admin')   => route('admin.dashboard', absolute: false),
            $this->can('akses dashboard petugas') => route('petugas.dashboard', absolute: false),
            default                               => route('buku.index', absolute: false),
        };
    }
}
