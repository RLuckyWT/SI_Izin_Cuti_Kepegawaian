<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenggunaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nip' => $this->nip,
            'nama' => $this->nama,
            'email' => $this->email,
            'role' => $this->role,
            'status_aktif' => (bool) $this->status_aktif,
            'pegawai' => $this->whenLoaded('pegawai', function () {
                if (! $this->pegawai) {
                    return null;
                }
                return [
                    'id' => $this->pegawai->id,
                    'jabatan' => $this->pegawai->jabatan,
                    'departemen' => $this->pegawai->departemen,
                    'status_kepegawaian' => $this->pegawai->status_kepegawaian,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}