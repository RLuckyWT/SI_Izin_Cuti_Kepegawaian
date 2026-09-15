<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisIzin extends Model
{
    protected $table = 'jenis_izin';

    protected $fillable = [
        'nama_jenis',
        'batas_maksimal_per_bulan',
        'keterangan',
    ];

    protected $casts = [
        'batas_maksimal_per_bulan' => 'integer',
    ];

    public function pengajuanIzin(): HasMany
    {
        return $this->hasMany(PengajuanIzin::class, 'jenis_izin_id');
    }
}