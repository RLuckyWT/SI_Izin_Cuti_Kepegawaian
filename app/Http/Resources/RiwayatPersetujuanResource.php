<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiwayatPersetujuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipe_pengajuan' => $this->tipe_pengajuan,
            'pengajuan_id' => $this->pengajuan_id,
            'pengajuan' => $this->buildPengajuanSummary(),
            'pengguna' => [
                'id' => $this->pengguna?->id,
                'nip' => $this->pengguna?->nip,
                'nama' => $this->pengguna?->nama,
                'role' => $this->pengguna?->role,
            ],
            'aksi' => $this->aksi,
            'catatan' => $this->catatan,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Ringkasan pengajuan sesuai tipenya.
     * Return null kalau relasi tidak di-load.
     */
    private function buildPengajuanSummary(): ?array
    {
        if (! $this->relationLoaded('pengajuan') || ! $this->pengajuan) {
            return null;
        }

        if ($this->tipe_pengajuan === 'cuti') {
            return [
                'id' => $this->pengajuan->id,
                'jenis' => 'cuti',
                'pemohon' => [
                    'id' => $this->pengajuan->pengguna?->id,
                    'nama' => $this->pengajuan->pengguna?->nama,
                ],
                'tanggal_mulai' => $this->pengajuan->tanggal_mulai?->format('Y-m-d'),
                'tanggal_selesai' => $this->pengajuan->tanggal_selesai?->format('Y-m-d'),
                'jumlah_hari' => $this->pengajuan->jumlah_hari,
                'status' => $this->pengajuan->status,
            ];
        }

        if ($this->tipe_pengajuan === 'izin') {
            return [
                'id' => $this->pengajuan->id,
                'jenis' => 'izin',
                'pemohon' => [
                    'id' => $this->pengajuan->pengguna?->id,
                    'nama' => $this->pengajuan->pengguna?->nama,
                ],
                'tanggal' => $this->pengajuan->tanggal?->format('Y-m-d'),
                'jam_mulai' => $this->pengajuan->jam_mulai,
                'jam_selesai' => $this->pengajuan->jam_selesai,
                'status' => $this->pengajuan->status,
            ];
        }

        return null;
    }
}