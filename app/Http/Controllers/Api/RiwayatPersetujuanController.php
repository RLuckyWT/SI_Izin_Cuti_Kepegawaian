<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiwayatPersetujuanResource;
use App\Models\PengajuanCuti;
use App\Models\PengajuanIzin;
use App\Models\RiwayatPersetujuan;
use Illuminate\Http\Request;

class RiwayatPersetujuanController extends Controller
{
    // GET /api/riwayat-persetujuan
    // HRD/Pimpinan: semua riwayat
    // Karyawan: hanya riwayat pengajuan miliknya
    public function index(Request $request)
    {
        $user = $request->user();

        $query = RiwayatPersetujuan::with([
            'pengguna',
            'pengajuan.pengguna', // eager load pengguna dari pengajuan (polymorphic)
        ]);

        // Karyawan: batasi ke pengajuan milik sendiri
        if ($user->isKaryawan()) {
            $cutiIds = PengajuanCuti::where('pengguna_id', $user->id)->pluck('id');
            $izinIds = PengajuanIzin::where('pengguna_id', $user->id)->pluck('id');

            $query->where(function ($q) use ($cutiIds, $izinIds) {
                $q->where(function ($sub) use ($cutiIds) {
                    $sub->where('tipe_pengajuan', 'cuti')
                        ->whereIn('pengajuan_id', $cutiIds);
                })->orWhere(function ($sub) use ($izinIds) {
                    $sub->where('tipe_pengajuan', 'izin')
                        ->whereIn('pengajuan_id', $izinIds);
                });
            });
        }

        // Filter tambahan
        if ($request->filled('tipe_pengajuan')) {
            $query->where('tipe_pengajuan', $request->tipe_pengajuan);
        }
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }
        if ($request->filled('pengguna_id')) {
            // Filter berdasarkan approver
            $query->where('pengguna_id', $request->pengguna_id);
        }
        if ($request->filled('pengajuan_id') && $request->filled('tipe_pengajuan')) {
            $query->where('pengajuan_id', $request->pengajuan_id);
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        $data = $query->orderByDesc('created_at')->paginate(20);

        return RiwayatPersetujuanResource::collection($data);
    }

    // GET /api/riwayat-persetujuan/{id}
    public function show(Request $request, RiwayatPersetujuan $riwayatPersetujuan)
    {
        $user = $request->user();

        // Karyawan: cek apakah pengajuan ini miliknya
        if ($user->isKaryawan() && ! $this->isOwnedByKaryawan($riwayatPersetujuan, $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        return new RiwayatPersetujuanResource(
            $riwayatPersetujuan->load(['pengguna', 'pengajuan.pengguna'])
        );
    }

    // GET /api/pengajuan-cuti/{id}/riwayat
    // Helper: semua riwayat untuk 1 pengajuan cuti tertentu
    public function forCuti(Request $request, PengajuanCuti $pengajuanCuti)
    {
        $user = $request->user();

        if ($user->isKaryawan() && $pengajuanCuti->pengguna_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $data = RiwayatPersetujuan::with('pengguna')
            ->where('tipe_pengajuan', 'cuti')
            ->where('pengajuan_id', $pengajuanCuti->id)
            ->orderBy('created_at')
            ->get();

        return RiwayatPersetujuanResource::collection($data);
    }

    // GET /api/pengajuan-izin/{id}/riwayat
    public function forIzin(Request $request, PengajuanIzin $pengajuanIzin)
    {
        $user = $request->user();

        if ($user->isKaryawan() && $pengajuanIzin->pengguna_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $data = RiwayatPersetujuan::with('pengguna')
            ->where('tipe_pengajuan', 'izin')
            ->where('pengajuan_id', $pengajuanIzin->id)
            ->orderBy('created_at')
            ->get();

        return RiwayatPersetujuanResource::collection($data);
    }

    // Helper: cek apakah pengajuan milik karyawan tertentu
    private function isOwnedByKaryawan(RiwayatPersetujuan $riwayat, int $penggunaId): bool
    {
        if ($riwayat->tipe_pengajuan === 'cuti') {
            return PengajuanCuti::where('id', $riwayat->pengajuan_id)
                ->where('pengguna_id', $penggunaId)
                ->exists();
        }

        if ($riwayat->tipe_pengajuan === 'izin') {
            return PengajuanIzin::where('id', $riwayat->pengajuan_id)
                ->where('pengguna_id', $penggunaId)
                ->exists();
        }

        return false;
    }
}