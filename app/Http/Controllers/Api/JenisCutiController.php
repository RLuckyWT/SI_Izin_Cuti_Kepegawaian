<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JenisCutiResource;
use App\Models\JenisCuti;
use Illuminate\Http\Request;

class JenisCutiController extends Controller
{
    // GET /api/jenis-cuti
    public function index(Request $request)
    {
        $query = JenisCuti::query();

        if ($request->filled('search')) {
            $query->where('nama_jenis', 'like', '%' . $request->search . '%');
        }

        // Kalau tidak minta paginasi, kembalikan semua (untuk dropdown)
        if ($request->boolean('all')) {
            return JenisCutiResource::collection($query->orderBy('nama_jenis')->get());
        }

        $data = $query->orderBy('nama_jenis')->paginate(15);

        return JenisCutiResource::collection($data);
    }

    // POST /api/jenis-cuti  (khusus HRD)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jenis' => 'required|string|max:50|unique:jenis_cuti,nama_jenis',
            'kuota_default_hari' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $jenisCuti = JenisCuti::create($validated);

        return (new JenisCutiResource($jenisCuti))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/jenis-cuti/{id}
    public function show(JenisCuti $jenisCuti)
    {
        return new JenisCutiResource($jenisCuti);
    }

    // PUT /api/jenis-cuti/{id}  (khusus HRD)
    public function update(Request $request, JenisCuti $jenisCuti)
    {
        $validated = $request->validate([
            'nama_jenis' => 'sometimes|required|string|max:50|unique:jenis_cuti,nama_jenis,' . $jenisCuti->id,
            'kuota_default_hari' => 'sometimes|required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $jenisCuti->update($validated);

        return new JenisCutiResource($jenisCuti->fresh());
    }

    // DELETE /api/jenis-cuti/{id}  (khusus HRD)
    public function destroy(JenisCuti $jenisCuti)
    {
        // Cek apakah masih dipakai di pengajuan_cuti
        if ($jenisCuti->pengajuanCuti()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis cuti tidak bisa dihapus karena sudah dipakai pada pengajuan.',
            ], 422);
        }

        $jenisCuti->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jenis cuti berhasil dihapus.',
        ]);
    }
}