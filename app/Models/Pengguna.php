<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nip', 'nama', 'email', 'password', 'role', 'status_aktif',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    // Relasi
    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, 'pengguna_id');
    }

    public function kuotaCuti()
    {
        return $this->hasMany(KuotaCuti::class, 'pengguna_id');
    }

    public function pengajuanCuti()
    {
        return $this->hasMany(PengajuanCuti::class, 'pengguna_id');
    }

    public function pengajuanIzin()
    {
        return $this->hasMany(PengajuanIzin::class, 'pengguna_id');
    }
}