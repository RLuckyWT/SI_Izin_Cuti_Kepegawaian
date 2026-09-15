<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuotaCuti extends Model
{
    protected $table = 'kuota_cuti';

    protected $fillable = [
        'pengguna_id', 'jenis_cuti_id', 'tahun',
        'kuota_total', 'kuota_terpakai', 'kuota_sisa',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'kuota_total' => 'integer',
        'kuota_terpakai' => 'integer',
        'kuota_sisa' => 'integer',
    ];

    /**
     * Auto-hitung kuota_sisa sebelum disimpan.
     */
    protected static function booted(): void
    {
        static::saving(function ($model) {
            $model->kuota_sisa = max(0, $model->kuota_total - $model->kuota_terpakai);
        });
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }

    /**
     * Hitung ulang kuota_sisa = kuota_total - kuota_terpakai
     */
    public function recalculateSisa(): self
    {
        $this->kuota_sisa = max(0, $this->kuota_total - $this->kuota_terpakai);
        return $this;
    }

    /**
     * Cek apakah kuota masih cukup untuk N hari
     */
    public function isCukup(int $jumlahHari): bool
    {
        return $this->kuota_sisa >= $jumlahHari;
    }
}