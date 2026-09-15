<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PegawaiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pengguna' => [
                'id' => $this->pengguna?->id,
                'nip' => $this->pengguna?->nip,
                'nama' => $this->pengguna?->nama,
                'email' => $this->pengguna?->email,
                'role' => $this->pengguna?->role,
                'status_aktif' => $this->pengguna?->status_aktif,
            ],
            'jabatan' => $this->jabatan,
            'departemen' => $this->departemen,
            'jenis_kelamin' => $this->jenis_kelamin,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'no_telepon' => $this->no_telepon,
            'alamat' => $this->alamat,
            'tanggal_masuk_kerja' => $this->tanggal_masuk_kerja?->format('Y-m-d'),
            'status_kepegawaian' => $this->status_kepegawaian,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}