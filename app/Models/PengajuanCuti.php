<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanCuti extends Model
{
    protected $table = 'pengajuan_cuti';

    protected $fillable = [
        'pengguna_id', 'jenis_cuti_id', 'tanggal_mulai', 'tanggal_selesai',
        'jumlah_hari', 'alasan', 'file_lampiran', 'status',
        'disetujui_oleh', 'catatan_persetujuan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatPersetujuan::class, 'pengajuan_id')
            ->where('tipe_pengajuan', 'cuti');
    }
}