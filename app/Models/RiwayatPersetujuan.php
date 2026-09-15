<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\MorphTo;
=======
>>>>>>> 4cb41376eb552479c87016f1932acaaae960737c

class RiwayatPersetujuan extends Model
{
    protected $table = 'riwayat_persetujuan';

    protected $fillable = [
<<<<<<< HEAD
        'tipe_pengajuan',
        'pengajuan_id',
        'pengguna_id',
        'aksi',
        'catatan',
    ];

    // Yang melakukan aksi (approver/rejecter)
=======
        'tipe_pengajuan', 'pengajuan_id', 'pengguna_id', 'aksi', 'catatan',
    ];

>>>>>>> 4cb41376eb552479c87016f1932acaaae960737c
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
<<<<<<< HEAD

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
=======
>>>>>>> 4cb41376eb552479c87016f1932acaaae960737c
}