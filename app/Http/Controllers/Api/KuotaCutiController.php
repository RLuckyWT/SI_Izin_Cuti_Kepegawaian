<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KuotaCutiResource;
use App\Models\JenisCuti;
use App\Models\KuotaCuti;
use App\Models\User;
use Illuminate\Http\Request;

class KuotaCutiController extends Controller
{
    // GET /api/kuota-cuti
    // - Karyawan: hanya kuota milik sendiri
    // - HRD/Pimpinan: bisa lihat semua (filter tahun, pengguna_id)
    public function index(Request $request)
    {
        $user = $request->user();
        $query = KuotaCuti::with(['pengguna', 'jenisCuti']);

        if ($user->isKaryawan()) {
            $query->where('pengguna_id', $user->id);
        } elseif ($request->filled('pengguna_id')) {
            $query->where('pengguna_id', $request->pengguna_id);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        } else {
            // Default: tahun sekarang
            $query->where('tahun', now()->year);
        }

        if ($request->filled('jenis_cuti_id')) {
            $query->where('jenis_cuti_id', $request->jenis_cuti_id);
        }

        return KuotaCutiResource::collection(
            $query->orderBy('jenis_cuti_id')->get()
        );
    }

    // GET /api/kuota-cuti/{id}
    public function show(Request $request, KuotaCuti $kuotaCuti)
    {
        if ($request->user()->isKaryawan()
            && $kuotaCuti->pengguna_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        return new KuotaCutiResource($kuotaCuti->load(['pengguna', 'jenisCuti']));
    }

    // POST /api/kuota-cuti  (khusus HRD)
    // Buat kuota untuk 1 pegawai + 1 jenis cuti + 1 tahun
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pengguna_id' => 'required|exists:pengguna,id',
            'jenis_cuti_id' => 'required|exists:jenis_cuti,id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'kuota_total' => 'required|integer|min:0',
            'kuota_terpakai' => 'nullable|integer|min:0',
        ]);

        // Cek duplikat (unique constraint)
        $exists = KuotaCuti::where('pengguna_id', $validated['pengguna_id'])
            ->where('jenis_cuti_id', $validated['jenis_cuti_id'])
            ->where('tahun', $validated['tahun'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kuota untuk pegawai, jenis cuti, dan tahun tersebut sudah ada.',
            ], 422);
        }

        $kuotaCuti = new KuotaCuti($validated);
        $kuotaCuti->kuota_terpakai = $validated['kuota_terpakai'] ?? 0;
        $kuotaCuti->recalculateSisa();
        $kuotaCuti->save();

        return (new KuotaCutiResource($kuotaCuti->load(['pengguna', 'jenisCuti'])))
            ->response()
            ->setStatusCode(201);
    }

    // PUT /api/kuota-cuti/{id}  (khusus HRD)
    public function update(Request $request, KuotaCuti $kuotaCuti)
    {
        $validated = $request->validate([
            'kuota_total' => 'sometimes|required|integer|min:0',
            'kuota_terpakai' => 'sometimes|required|integer|min:0',
        ]);

        if (isset($validated['kuota_total'])) {
            $kuotaCuti->kuota_total = $validated['kuota_total'];
        }
        if (isset($validated['kuota_terpakai'])) {
            $kuotaCuti->kuota_terpakai = $validated['kuota_terpakai'];
        }

        $kuotaCuti->recalculateSisa();
        $kuotaCuti->save();

        return new KuotaCutiResource($kuotaCuti->fresh(['pengguna', 'jenisCuti']));
    }

    // DELETE /api/kuota-cuti/{id}  (khusus HRD)
    public function destroy(KuotaCuti $kuotaCuti)
    {
        $kuotaCuti->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kuota cuti berhasil dihapus.',
        ]);
    }

    // POST /api/kuota-cuti/generate  (khusus HRD)
    // Generate kuota untuk SEMUA pegawai, semua jenis cuti, untuk tahun tertentu
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $tahun = $validated['tahun'];
        $pegawaiIds = User::whereIn('role', ['karyawan', 'hrd', 'pimpinan'])
            ->pluck('id');
        $jenisCutiList = JenisCuti::all();

        $created = 0;
        $skipped = 0;

        foreach ($pegawaiIds as $penggunaId) {
            foreach ($jenisCutiList as $jenis) {
                $exists = KuotaCuti::where('pengguna_id', $penggunaId)
                    ->where('jenis_cuti_id', $jenis->id)
                    ->where('tahun', $tahun)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                KuotaCuti::create([
                    'pengguna_id' => $penggunaId,
                    'jenis_cuti_id' => $jenis->id,
                    'tahun' => $tahun,
                    'kuota_total' => $jenis->kuota_default_hari,
                    'kuota_terpakai' => 0,
                    'kuota_sisa' => $jenis->kuota_default_hari,
                ]);

                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Generate selesai. Dibuat: {$created}, dilewati (sudah ada): {$skipped}.",
            'data' => [
                'tahun' => $tahun,
                'created' => $created,
                'skipped' => $skipped,
            ],
        ]);
    }
}