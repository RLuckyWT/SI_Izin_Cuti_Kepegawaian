<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengajuanIzinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pengguna' => [
                'id' => $this->pengguna?->id,
                'nip' => $this->pengguna?->nip,
                'nama' => $this->pengguna?->nama,
            ],
            'jenis_izin' => [
                'id' => $this->jenisIzin?->id,
                'nama_jenis' => $this->jenisIzin?->nama_jenis,
            ],
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'alasan' => $this->alasan,
            'file_lampiran' => $this->file_lampiran
                ? asset('storage/' . $this->file_lampiran)
                : null,
            'status' => $this->status,
            'disetujui_oleh' => $this->penyetuju ? [
                'id' => $this->penyetuju->id,
                'nama' => $this->penyetuju->nama,
                'role' => $this->penyetuju->role,
            ] : null,
            'catatan_persetujuan' => $this->catatan_persetujuan,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}