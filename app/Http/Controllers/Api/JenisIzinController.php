<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JenisIzinResource;
use App\Models\JenisIzin;
use Illuminate\Http\Request;

class JenisIzinController extends Controller
{
    // GET /api/jenis-izin
    public function index(Request $request)
    {
        $query = JenisIzin::query();

        if ($request->filled('search')) {
            $query->where('nama_jenis', 'like', '%' . $request->search . '%');
        }

        if ($request->boolean('all')) {
            return JenisIzinResource::collection($query->orderBy('nama_jenis')->get());
        }

        $data = $query->orderBy('nama_jenis')->paginate(15);

        return JenisIzinResource::collection($data);
    }

    // POST /api/jenis-izin  (khusus HRD)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jenis' => 'required|string|max:50|unique:jenis_izin,nama_jenis',
            'batas_maksimal_per_bulan' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $jenisIzin = JenisIzin::create($validated);

        return (new JenisIzinResource($jenisIzin))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/jenis-izin/{id}
    public function show(JenisIzin $jenisIzin)
    {
        return new JenisIzinResource($jenisIzin);
    }

    // PUT /api/jenis-izin/{id}  (khusus HRD)
    public function update(Request $request, JenisIzin $jenisIzin)
    {
        $validated = $request->validate([
            'nama_jenis' => 'sometimes|required|string|max:50|unique:jenis_izin,nama_jenis,' . $jenisIzin->id,
            'batas_maksimal_per_bulan' => 'sometimes|required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $jenisIzin->update($validated);

        return new JenisIzinResource($jenisIzin->fresh());
    }

    // DELETE /api/jenis-izin/{id}  (khusus HRD)
    public function destroy(JenisIzin $jenisIzin)
    {
        if ($jenisIzin->pengajuanIzin()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis izin tidak bisa dihapus karena sudah dipakai pada pengajuan.',
            ], 422);
        }

        $jenisIzin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jenis izin berhasil dihapus.',
        ]);
    }
}