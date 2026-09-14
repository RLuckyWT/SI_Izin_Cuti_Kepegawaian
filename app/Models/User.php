<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nip', 'nama', 'email', 'password', 'role', 'status_aktif',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'status_aktif' => 'boolean',
        'password' => 'hashed',
    ];

    public function pegawai(): HasOne
    {
        return $this->hasOne(Pegawai::class, 'pengguna_id');
    }

    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'pengguna_id');
    }

    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    public function isHrd(): bool
    {
        return $this->role === 'hrd';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }
}