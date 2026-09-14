<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPersetujuan extends Model
{
    protected $table = 'riwayat_persetujuan';

    protected $fillable = [
        'tipe_pengajuan', 'pengajuan_id', 'pengguna_id', 'aksi', 'catatan',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}