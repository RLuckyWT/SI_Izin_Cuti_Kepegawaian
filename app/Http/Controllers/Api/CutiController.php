<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuti; // <-- sesuaikan nama model

class CutiController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Cuti::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string',
        ]);

        $cuti = Cuti::create($validated);

        return response()->json([
            'success' => true,
            'data' => $cuti,
        ], 201);
    }

    public function show(string $id)
    {
        $cuti = Cuti::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $cuti,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $cuti = Cuti::findOrFail($id);
        $cuti->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $cuti,
        ]);
    }

    public function destroy(string $id)
    {
        Cuti::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data cuti dihapus',
        ]);
    }
}