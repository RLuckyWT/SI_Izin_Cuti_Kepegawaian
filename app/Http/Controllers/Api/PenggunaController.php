<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenggunaResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    // GET /api/pengguna  (khusus HRD)
    public function index(Request $request)
    {
        $query = User::with('pegawai');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status_aktif')) {
            $query->where('status_aktif', $request->boolean('status_aktif'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $data = $query->orderByDesc('id')->paginate(15);

        return PenggunaResource::collection($data);
    }

    // GET /api/pengguna/{id}  (khusus HRD)
    public function show(User $pengguna)
    {
        return new PenggunaResource($pengguna->load('pegawai'));
    }

    // POST /api/pengguna  (khusus HRD)
    // Buat akun tanpa data pegawai (misal akun admin sistem)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|max:20|unique:pengguna,nip',
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:pengguna,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['karyawan', 'hrd', 'pimpinan'])],
            'status_aktif' => 'boolean',
        ]);

        $pengguna = User::create([
            'nip' => $validated['nip'],
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password' => $validated['password'], // auto-hash via cast
            'role' => $validated['role'],
            'status_aktif' => $validated['status_aktif'] ?? true,
        ]);

        return (new PenggunaResource($pengguna))
            ->response()
            ->setStatusCode(201);
    }

    // PUT /api/pengguna/{id}  (khusus HRD)
    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'nip' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('pengguna', 'nip')->ignore($pengguna->id)],
            'nama' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', 'max:100', Rule::unique('pengguna', 'email')->ignore($pengguna->id)],
            'role' => ['sometimes', 'required', Rule::in(['karyawan', 'hrd', 'pimpinan'])],
            'status_aktif' => 'sometimes|boolean',
        ]);

        $pengguna->update($validated);

        return new PenggunaResource($pengguna->fresh(['pegawai']));
    }

    // DELETE /api/pengguna/{id}  (khusus HRD)
    // ⚠️ Cascade: tabel pegawai, pengajuan_cuti, pengajuan_izin,
    //    riwayat_persetujuan, kuota_cuti juga akan terhapus
    public function destroy(Request $request, User $pengguna)
    {
        // Cegah HRD hapus dirinya sendiri
        if ($pengguna->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus akun milik sendiri.',
            ], 422);
        }

        $pengguna->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus.',
        ]);
    }

    // PATCH /api/pengguna/{id}/toggle-aktif  (khusus HRD)
    public function toggleAktif(Request $request, User $pengguna)
    {
        if ($pengguna->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menonaktifkan akun sendiri.',
            ], 422);
        }

        $pengguna->status_aktif = ! $pengguna->status_aktif;
        $pengguna->save();

        // Kalau dinonaktifkan, hapus semua token aktif
        if (! $pengguna->status_aktif) {
            $pengguna->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $pengguna->status_aktif
                ? 'Akun berhasil diaktifkan.'
                : 'Akun berhasil dinonaktifkan.',
            'data' => new PenggunaResource($pengguna->fresh(['pegawai'])),
        ]);
    }

    // POST /api/pengguna/{id}/reset-password  (khusus HRD)
    public function resetPassword(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'password' => 'nullable|string|min:6',
        ]);

        // Kalau HRD tidak kasih password, generate otomatis
        $passwordBaru = $validated['password'] ?? 'password' . rand(1000, 9999);

        $pengguna->password = $passwordBaru;
        $pengguna->save();

        // Hapus semua token aktif (paksa login ulang)
        $pengguna->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Beritahukan password baru ke pegawai.',
            'data' => [
                'nip' => $pengguna->nip,
                'nama' => $pengguna->nama,
                'email' => $pengguna->email,
                'password_baru' => $passwordBaru,
            ],
        ]);
    }
}