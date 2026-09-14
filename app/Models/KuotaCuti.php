<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuotaCuti extends Model
{
    protected $table = 'kuota_cuti';

    protected $fillable = [
        'pengguna_id', 'jenis_cuti_id', 'tahun',
        'kuota_total', 'kuota_terpakai', 'kuota_sisa',
    ];

    // Auto-hitung kuota_sisa sebelum disimpan
    protected static function booted(): void
    {
        static::saving(function ($model) {
            $model->kuota_sisa = $model->kuota_total - $model->kuota_terpakai;
        });
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function jenisCuti()
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }
}