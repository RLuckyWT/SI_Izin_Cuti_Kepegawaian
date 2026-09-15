<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanIzin extends Model
{
    protected $table = 'pengajuan_izin';

    protected $fillable = [
        'pengguna_id', 'jenis_izin_id', 'tanggal',
        'jam_mulai', 'jam_selesai', 'alasan', 'file_lampiran',
        'status', 'disetujui_oleh', 'catatan_persetujuan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function jenisIzin(): BelongsTo
    {
        return $this->belongsTo(JenisIzin::class, 'jenis_izin_id');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatPersetujuan::class, 'pengajuan_id')
            ->where('tipe_pengajuan', 'izin');
    }
}
=======

class PengajuanIzin extends Model
{
    //
}
>>>>>>> 4cb41376eb552479c87016f1932acaaae960737c
