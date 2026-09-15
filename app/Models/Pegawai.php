<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = [
<<<<<<< HEAD
        'pengguna_id',
        'jabatan',
        'departemen',
        'jenis_kelamin',
        'tanggal_lahir',
        'no_telepon',
        'alamat',
        'tanggal_masuk_kerja',
        'status_kepegawaian',
=======
        'pengguna_id', 'jabatan', 'departemen', 'jenis_kelamin',
        'tanggal_lahir', 'no_telepon', 'alamat',
        'tanggal_masuk_kerja', 'status_kepegawaian',
>>>>>>> 4cb41376eb552479c87016f1932acaaae960737c
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk_kerja' => 'date',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}