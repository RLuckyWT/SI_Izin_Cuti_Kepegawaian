<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengajuanCutiResource extends JsonResource
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
            'jenis_cuti' => [
                'id' => $this->jenisCuti?->id,
<<<<<<< HEAD
                'nama' => $this->jenisCuti?->nama_jenis,
                'kuota_default_hari' => $this->jenisCuti?->kuota_default_hari,
                'keterangan' => $this->jenisCuti?->keterangan,
=======
                'nama' => $this->jenisCuti?->nama,
>>>>>>> 4cb41376eb552479c87016f1932acaaae960737c
            ],
            'tanggal_mulai' => $this->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai' => $this->tanggal_selesai?->format('Y-m-d'),
            'jumlah_hari' => $this->jumlah_hari,
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