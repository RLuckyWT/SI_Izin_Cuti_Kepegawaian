<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenggunaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    // PUT /api/me/profile  (semua role)
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', 'max:100',
                        Rule::unique('pengguna', 'email')->ignore($user->id)],
        ]);

        // NIP sengaja TIDAK bisa diubah sendiri (harus HRD)
        $user->update($validated);

        return new PenggunaResource($user->fresh(['pegawai']));
    }

    // PUT /api/me/password  (semua role)
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($validated['password_lama'], $user->password)) {
            throw ValidationException::withMessages([
                'password_lama' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->password = $validated['password_baru'];
        $user->save();

        // Opsional: hapus token lain, kecuali token yang sedang dipakai
        $currentTokenId = $request->user()->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Token lain yang aktif sudah logout.',
        ]);
    }
}