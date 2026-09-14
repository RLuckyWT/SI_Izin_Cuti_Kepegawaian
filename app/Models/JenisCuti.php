<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisCuti extends Model
{
    protected $table = 'jenis_cuti';

    protected $fillable = ['nama', 'kuota_default', 'keterangan'];

    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'jenis_cuti_id');
    }
}