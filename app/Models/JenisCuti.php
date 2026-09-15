<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisCuti extends Model
{
    protected $table = 'jenis_cuti';

    protected $fillable = [
        'nama_jenis',
        'kuota_default_hari',
        'keterangan',
    ];

    protected $casts = [
        'kuota_default_hari' => 'integer',
    ];

    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'jenis_cuti_id');
    }
}