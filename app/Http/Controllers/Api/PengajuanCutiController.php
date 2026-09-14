<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengajuanCutiResource;
use App\Models\PengajuanCuti;
use App\Models\RiwayatPersetujuan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengajuanCutiController extends Controller
{
    // GET /api/pengajuan-cuti
    public function index(Request $request)
    {
        $user = $request->user();
        $query = PengajuanCuti::with(['pengguna', 'jenisCuti', 'penyetuju']);

        // Karyawan hanya lihat pengajuan sendiri
        if ($user->isKaryawan()) {
            $query->where('pengguna_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderByDesc('created_at')->paginate(15);

        return PengajuanCutiResource::collection($data);
    }

    // POST /api/pengajuan-cuti
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_cuti_id' => 'required|exists:jenis_cuti,id',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string',
            'file_lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $mulai = Carbon::parse($validated['tanggal_mulai']);
        $selesai = Carbon::parse($validated['tanggal_selesai']);

        $filePath = $request->hasFile('file_lampiran')
            ? $request->file('file_lampiran')->store('lampiran_cuti', 'public')
            : null;

        $pengajuan = PengajuanCuti::create([
            'pengguna_id' => $request->user()->id,
            'jenis_cuti_id' => $validated['jenis_cuti_id'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jumlah_hari' => $mulai->diffInDays($selesai) + 1,
            'alasan' => $validated['alasan'],
            'file_lampiran' => $filePath,
            'status' => 'menunggu',
        ]);

        return (new PengajuanCutiResource($pengajuan->load(['pengguna', 'jenisCuti'])))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/pengajuan-cuti/{id}
    public function show(Request $request, PengajuanCuti $pengajuanCuti)
    {
        if ($request->user()->isKaryawan()
            && $pengajuanCuti->pengguna_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return new PengajuanCutiResource(
            $pengajuanCuti->load(['pengguna', 'jenisCuti', 'penyetuju', 'riwayat.pengguna'])
        );
    }

    // PUT /api/pengajuan-cuti/{id}
    public function update(Request $request, PengajuanCuti $pengajuanCuti)
    {
        if ($pengajuanCuti->pengguna_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        if ($pengajuanCuti->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Pengajuan sudah diproses, tidak bisa diubah.'], 422);
        }

        $validated = $request->validate([
            'jenis_cuti_id' => 'sometimes|exists:jenis_cuti,id',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_selesai' => 'sometimes|date|after_or_equal:tanggal_mulai',
            'alasan' => 'sometimes|string',
        ]);

        if (isset($validated['tanggal_mulai']) || isset($validated['tanggal_selesai'])) {
            $mulai = Carbon::parse($validated['tanggal_mulai'] ?? $pengajuanCuti->tanggal_mulai);
            $selesai = Carbon::parse($validated['tanggal_selesai'] ?? $pengajuanCuti->tanggal_selesai);
            $validated['jumlah_hari'] = $mulai->diffInDays($selesai) + 1;
        }

        $pengajuanCuti->update($validated);

        return new PengajuanCutiResource(
            $pengajuanCuti->fresh(['pengguna', 'jenisCuti', 'penyetuju'])
        );
    }

    // DELETE /api/pengajuan-cuti/{id}
    public function destroy(Request $request, PengajuanCuti $pengajuanCuti)
    {
        if ($pengajuanCuti->pengguna_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        if ($pengajuanCuti->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Pengajuan sudah diproses, tidak bisa dibatalkan.'], 422);
        }

        $pengajuanCuti->delete();

        return response()->json(['success' => true, 'message' => 'Pengajuan dibatalkan.']);
    }

    // PATCH /api/pengajuan-cuti/{id}/approve-hrd  (khusus HRD)
    public function approveHrd(Request $request, PengajuanCuti $pengajuanCuti)
    {
        if ($pengajuanCuti->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Status tidak valid untuk approve HRD.'], 422);
        }

        $validated = $request->validate([
            'catatan_persetujuan' => 'nullable|string',
        ]);

        $pengajuanCuti->update([
            'status' => 'disetujui_hrd',
            'disetujui_oleh' => $request->user()->id,
            'catatan_persetujuan' => $validated['catatan_persetujuan'] ?? null,
        ]);

        RiwayatPersetujuan::create([
            'tipe_pengajuan' => 'cuti',
            'pengajuan_id' => $pengajuanCuti->id,
            'pengguna_id' => $request->user()->id,
            'aksi' => 'disetujui',
            'catatan' => $validated['catatan_persetujuan'] ?? null,
        ]);

        return new PengajuanCutiResource(
            $pengajuanCuti->fresh(['pengguna', 'jenisCuti', 'penyetuju'])
        );
    }

    // PATCH /api/pengajuan-cuti/{id}/approve-pimpinan  (khusus Pimpinan)
    public function approvePimpinan(Request $request, PengajuanCuti $pengajuanCuti)
    {
        if ($pengajuanCuti->status !== 'disetujui_hrd') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan belum disetujui HRD.',
            ], 422);
        }

        $validated = $request->validate([
            'catatan_persetujuan' => 'nullable|string',
        ]);

        $pengajuanCuti->update([
            'status' => 'disetujui_pimpinan',
            'disetujui_oleh' => $request->user()->id,
            'catatan_persetujuan' => $validated['catatan_persetujuan'] ?? null,
        ]);

        RiwayatPersetujuan::create([
            'tipe_pengajuan' => 'cuti',
            'pengajuan_id' => $pengajuanCuti->id,
            'pengguna_id' => $request->user()->id,
            'aksi' => 'disetujui',
            'catatan' => $validated['catatan_persetujuan'] ?? null,
        ]);

        return new PengajuanCutiResource(
            $pengajuanCuti->fresh(['pengguna', 'jenisCuti', 'penyetuju'])
        );
    }

    // PATCH /api/pengajuan-cuti/{id}/reject  (HRD atau Pimpinan)
    public function reject(Request $request, PengajuanCuti $pengajuanCuti)
    {
        if (! in_array($pengajuanCuti->status, ['menunggu', 'disetujui_hrd'], true)) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid untuk ditolak.'], 422);
        }

        $validated = $request->validate([
            'catatan_persetujuan' => 'required|string',
        ]);

        $pengajuanCuti->update([
            'status' => 'ditolak',
            'disetujui_oleh' => $request->user()->id,
            'catatan_persetujuan' => $validated['catatan_persetujuan'],
        ]);

        RiwayatPersetujuan::create([
            'tipe_pengajuan' => 'cuti',
            'pengajuan_id' => $pengajuanCuti->id,
            'pengguna_id' => $request->user()->id,
            'aksi' => 'ditolak',
            'catatan' => $validated['catatan_persetujuan'],
        ]);

        return new PengajuanCutiResource(
            $pengajuanCuti->fresh(['pengguna', 'jenisCuti', 'penyetuju'])
        );
    }
}