<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RiwayatPersetujuan extends Model
{
    protected $table = 'riwayat_persetujuan';

    protected $fillable = [
        'tipe_pengajuan',
        'pengajuan_id',
        'pengguna_id',
        'aksi',
        'catatan',
    ];

    // Yang melakukan aksi (approver/rejecter)
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    // Pengajuan yang di-approve/reject (polymorphic)
    public function pengajuan(): MorphTo
    {
        return $this->morphTo('pengajuan', 'tipe_pengajuan', 'pengajuan_id');
    }

    // Scope: filter berdasarkan tipe
    public function scopeTipe($query, string $tipe)
    {
        return $query->where('tipe_pengajuan', $tipe);
    }

    // Scope: filter berdasarkan aksi
    public function scopeAksi($query, string $aksi)
    {
        return $query->where('aksi', $aksi);
    }
}