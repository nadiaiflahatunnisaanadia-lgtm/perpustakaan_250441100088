<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    public const DIAJUKAN     = 'diajukan';
    public const DIPINJAM     = 'dipinjam';
    public const DIKEMBALIKAN = 'dikembalikan';
    public const DITOLAK      = 'ditolak';

    protected $table = 'peminjamans';

    protected $fillable = [
        'anggota_id',
        'buku_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status',
        'diproses_oleh',
    ];

    protected $casts = [
        'tanggal_pinjam'  => 'date',
        'tanggal_kembali' => 'date',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }

    public function pemroses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    /** Peminjaman yang masih berjalan (menunggu persetujuan atau sedang dipinjam). */
    public function scopeAktif($query)
    {
        return $query->whereIn('status', [self::DIAJUKAN, self::DIPINJAM]);
    }

    /** Riwayat = peminjaman yang sudah selesai diproses. */
    public function scopeRiwayat($query)
    {
        return $query->whereIn('status', [self::DIKEMBALIKAN, self::DITOLAK]);
    }
}
