<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PegawaiResource;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PegawaiController extends Controller
{
    // GET /api/pegawai  (HRD & Pimpinan)
    public function index(Request $request)
    {
        $query = Pegawai::with('pengguna');

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('pengguna', function ($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        // Filter by departemen
        if ($request->filled('departemen')) {
            $query->where('departemen', 'like', '%' . $request->departemen . '%');
        }

        // Filter by status kepegawaian
        if ($request->filled('status_kepegawaian')) {
            $query->where('status_kepegawaian', $request->status_kepegawaian);
        }

        // Search by nama / nip / email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pengguna', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $data = $query->orderByDesc('id')->paginate(15);

        return PegawaiResource::collection($data);
    }

    // GET /api/pegawai/me  (semua role yang login)
    public function me(Request $request)
    {
        $pegawai = Pegawai::with('pengguna')
            ->where('pengguna_id', $request->user()->id)
            ->first();

        if (! $pegawai) {
            return response()->json([
                'success' => false,
                'message' => 'Data pegawai belum dibuat. Hubungi HRD.',
            ], 404);
        }

        return new PegawaiResource($pegawai);
    }

    // GET /api/pegawai/{id}
    // HRD/Pimpinan: bisa lihat siapa saja
    // Karyawan: hanya bisa lihat milik sendiri
    public function show(Request $request, Pegawai $pegawai)
    {
        $user = $request->user();

        if ($user->isKaryawan() && $pegawai->pengguna_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        return new PegawaiResource($pegawai->load('pengguna'));
    }

    // POST /api/pegawai  (khusus HRD)
    // Buat pengguna + pegawai sekaligus dalam satu transaksi
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Data pengguna
            'nip' => 'required|string|max:20|unique:pengguna,nip',
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:pengguna,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['karyawan', 'hrd', 'pimpinan'])],
            'status_aktif' => 'boolean',

            // Data pegawai
            'jabatan' => 'nullable|string|max:100',
            'departemen' => 'nullable|string|max:100',
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'tanggal_lahir' => 'nullable|date',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tanggal_masuk_kerja' => 'nullable|date',
            'status_kepegawaian' => ['nullable', Rule::in(['tetap', 'kontrak', 'magang'])],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $pengguna = User::create([
                'nip' => $validated['nip'],
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password' => $validated['password'], // otomatis di-hash via cast
                'role' => $validated['role'],
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            $pegawai = Pegawai::create([
                'pengguna_id' => $pengguna->id,
                'jabatan' => $validated['jabatan'] ?? null,
                'departemen' => $validated['departemen'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'no_telepon' => $validated['no_telepon'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'tanggal_masuk_kerja' => $validated['tanggal_masuk_kerja'] ?? null,
                'status_kepegawaian' => $validated['status_kepegawaian'] ?? 'kontrak',
            ]);

            return $pegawai;
        });

        return (new PegawaiResource($result->load('pengguna')))
            ->response()
            ->setStatusCode(201);
    }

    // PUT /api/pegawai/{id}
    // HRD: bisa edit semua field (termasuk data pengguna)
    // Pemilik sendiri: hanya bisa edit field terbatas di pegawai
    public function update(Request $request, Pegawai $pegawai)
    {
        $user = $request->user();
        $isSelf = $pegawai->pengguna_id === $user->id;
        $isHrd = $user->isHrd();

        if (! $isSelf && ! $isHrd) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        if ($isHrd) {
            // HRD bisa edit semua
            $validated = $request->validate([
                // Pengguna
                'nip' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('pengguna', 'nip')->ignore($pegawai->pengguna_id)],
                'nama' => 'sometimes|required|string|max:100',
                'email' => ['sometimes', 'required', 'email', 'max:100', Rule::unique('pengguna', 'email')->ignore($pegawai->pengguna_id)],
                'role' => ['sometimes', 'required', Rule::in(['karyawan', 'hrd', 'pimpinan'])],
                'status_aktif' => 'sometimes|boolean',

                // Pegawai
                'jabatan' => 'nullable|string|max:100',
                'departemen' => 'nullable|string|max:100',
                'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
                'tanggal_lahir' => 'nullable|date',
                'no_telepon' => 'nullable|string|max:20',
                'alamat' => 'nullable|string',
                'tanggal_masuk_kerja' => 'nullable|date',
                'status_kepegawaian' => ['nullable', Rule::in(['tetap', 'kontrak', 'magang'])],
            ]);

            DB::transaction(function () use ($validated, $pegawai) {
                // Update pengguna
                $penggunaFields = array_intersect_key($validated, array_flip([
                    'nip', 'nama', 'email', 'role', 'status_aktif',
                ]));
                if (! empty($penggunaFields)) {
                    $pegawai->pengguna->update($penggunaFields);
                }

                // Update pegawai
                $pegawaiFields = array_intersect_key($validated, array_flip([
                    'jabatan', 'departemen', 'jenis_kelamin', 'tanggal_lahir',
                    'no_telepon', 'alamat', 'tanggal_masuk_kerja', 'status_kepegawaian',
                ]));
                if (! empty($pegawaiFields)) {
                    $pegawai->update($pegawaiFields);
                }
            });

        } else {
            // Self: hanya field terbatas
            $validated = $request->validate([
                'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
                'tanggal_lahir' => 'nullable|date',
                'no_telepon' => 'nullable|string|max:20',
                'alamat' => 'nullable|string',
            ]);

            $pegawai->update($validated);
        }

        return new PegawaiResource($pegawai->fresh(['pengguna']));
    }

    // DELETE /api/pegawai/{id}  (khusus HRD)
    // Menghapus data pegawai, TIDAK menghapus akun pengguna
    public function destroy(Request $request, Pegawai $pegawai)
    {
        // Cegah HRD hapus diri sendiri
        if ($pegawai->pengguna_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus data pegawai milik sendiri.',
            ], 422);
        }

        $pegawai->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pegawai berhasil dihapus.',
        ]);
    }
}