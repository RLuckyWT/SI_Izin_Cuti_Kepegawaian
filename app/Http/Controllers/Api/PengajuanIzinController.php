<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengajuanIzinResource;
use App\Models\PengajuanIzin;
use App\Models\RiwayatPersetujuan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengajuanIzinController extends Controller
{
    // GET /api/pengajuan-izin
    public function index(Request $request)
    {
        $user = $request->user();
        $query = PengajuanIzin::with(['pengguna', 'jenisIzin', 'penyetuju']);

        if ($user->isKaryawan()) {
            $query->where('pengguna_id', $user->id);
        } elseif ($request->filled('pengguna_id')) {
            $query->where('pengguna_id', $request->pengguna_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal', '<=', $request->tanggal_sampai);
        }

        $data = $query->orderByDesc('tanggal')->orderByDesc('created_at')->paginate(15);

        return PengajuanIzinResource::collection($data);
    }

    // POST /api/pengajuan-izin
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_izin_id' => 'required|exists:jenis_izin,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'alasan' => 'required|string',
            'file_lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Validasi kuota per bulan untuk jenis izin ini
        $jenisIzin = \App\Models\JenisIzin::findOrFail($validated['jenis_izin_id']);
        $tanggal = Carbon::parse($validated['tanggal']);

        if ($jenisIzin->batas_maksimal_per_bulan > 0) {
            $jumlahBulanIni = PengajuanIzin::where('pengguna_id', $request->user()->id)
                ->where('jenis_izin_id', $jenisIzin->id)
                ->whereYear('tanggal', $tanggal->year)
                ->whereMonth('tanggal', $tanggal->month)
                ->whereIn('status', ['menunggu', 'disetujui'])
                ->count();

            if ($jumlahBulanIni >= $jenisIzin->batas_maksimal_per_bulan) {
                return response()->json([
                    'success' => false,
                    'message' => "Batas maksimal {$jenisIzin->batas_maksimal_per_bulan}x per bulan untuk jenis izin ini sudah tercapai.",
                ], 422);
            }
        }

        $filePath = $request->hasFile('file_lampiran')
            ? $request->file('file_lampiran')->store('lampiran_izin', 'public')
            : null;

        $pengajuan = PengajuanIzin::create([
            'pengguna_id' => $request->user()->id,
            'jenis_izin_id' => $validated['jenis_izin_id'],
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'] ?? null,
            'jam_selesai' => $validated['jam_selesai'] ?? null,
            'alasan' => $validated['alasan'],
            'file_lampiran' => $filePath,
            'status' => 'menunggu',
        ]);

        return (new PengajuanIzinResource($pengajuan->load(['pengguna', 'jenisIzin'])))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/pengajuan-izin/{id}
    public function show(Request $request, PengajuanIzin $pengajuanIzin)
    {
        if ($request->user()->isKaryawan()
            && $pengajuanIzin->pengguna_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return new PengajuanIzinResource(
            $pengajuanIzin->load(['pengguna', 'jenisIzin', 'penyetuju', 'riwayat.pengguna'])
        );
    }

    // PUT /api/pengajuan-izin/{id}
    public function update(Request $request, PengajuanIzin $pengajuanIzin)
    {
        if ($pengajuanIzin->pengguna_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        if ($pengajuanIzin->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Pengajuan sudah diproses.'], 422);
        }

        $validated = $request->validate([
            'jenis_izin_id' => 'sometimes|exists:jenis_izin,id',
            'tanggal' => 'sometimes|date',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i',
            'alasan' => 'sometimes|string',
        ]);

        $pengajuanIzin->update($validated);

        return new PengajuanIzinResource(
            $pengajuanIzin->fresh(['pengguna', 'jenisIzin', 'penyetuju'])
        );
    }

    // DELETE /api/pengajuan-izin/{id}
    public function destroy(Request $request, PengajuanIzin $pengajuanIzin)
    {
        if ($pengajuanIzin->pengguna_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        if ($pengajuanIzin->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Pengajuan sudah diproses, tidak bisa dibatalkan.'], 422);
        }

        $pengajuanIzin->delete();

        return response()->json(['success' => true, 'message' => 'Pengajuan izin dibatalkan.']);
    }

    // PATCH /api/pengajuan-izin/{id}/approve  (HRD atau Pimpinan)
    public function approve(Request $request, PengajuanIzin $pengajuanIzin)
    {
        if ($pengajuanIzin->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Status tidak valid untuk disetujui.'], 422);
        }

        $validated = $request->validate([
            'catatan_persetujuan' => 'nullable|string',
        ]);

        $pengajuanIzin->update([
            'status' => 'disetujui',
            'disetujui_oleh' => $request->user()->id,
            'catatan_persetujuan' => $validated['catatan_persetujuan'] ?? null,
        ]);

        RiwayatPersetujuan::create([
            'tipe_pengajuan' => 'izin',
            'pengajuan_id' => $pengajuanIzin->id,
            'pengguna_id' => $request->user()->id,
            'aksi' => 'disetujui',
            'catatan' => $validated['catatan_persetujuan'] ?? null,
        ]);

        return new PengajuanIzinResource(
            $pengajuanIzin->fresh(['pengguna', 'jenisIzin', 'penyetuju'])
        );
    }

    // PATCH /api/pengajuan-izin/{id}/reject  (HRD atau Pimpinan)
    public function reject(Request $request, PengajuanIzin $pengajuanIzin)
    {
        if ($pengajuanIzin->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Status tidak valid untuk ditolak.'], 422);
        }

        $validated = $request->validate([
            'catatan_persetujuan' => 'required|string',
        ]);

        $pengajuanIzin->update([
            'status' => 'ditolak',
            'disetujui_oleh' => $request->user()->id,
            'catatan_persetujuan' => $validated['catatan_persetujuan'],
        ]);

        RiwayatPersetujuan::create([
            'tipe_pengajuan' => 'izin',
            'pengajuan_id' => $pengajuanIzin->id,
            'pengguna_id' => $request->user()->id,
            'aksi' => 'ditolak',
            'catatan' => $validated['catatan_persetujuan'],
        ]);

        return new PengajuanIzinResource(
            $pengajuanIzin->fresh(['pengguna', 'jenisIzin', 'penyetuju'])
        );
    }
}