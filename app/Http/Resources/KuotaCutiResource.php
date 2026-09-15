<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KuotaCutiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tahun' => $this->tahun,
            'pengguna' => [
                'id' => $this->pengguna?->id,
                'nip' => $this->pengguna?->nip,
                'nama' => $this->pengguna?->nama,
            ],
            'jenis_cuti' => [
                'id' => $this->jenisCuti?->id,
                'nama_jenis' => $this->jenisCuti?->nama_jenis,
            ],
            'kuota_total' => $this->kuota_total,
            'kuota_terpakai' => $this->kuota_terpakai,
            'kuota_sisa' => $this->kuota_sisa,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}