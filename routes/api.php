<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PengajuanCutiController;
use App\Http\Controllers\Api\JenisCutiController;
use App\Http\Controllers\Api\JenisIzinController;
use App\Http\Controllers\Api\KuotaCutiController;
use App\Http\Controllers\Api\PengajuanIzinController;
use App\Http\Controllers\Api\PegawaiController;
use App\Http\Controllers\Api\PenggunaController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RiwayatPersetujuanController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Butuh login
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Pengajuan cuti — semua role
    Route::get('/pengajuan-cuti', [PengajuanCutiController::class, 'index']);
    Route::post('/pengajuan-cuti', [PengajuanCutiController::class, 'store']);
    Route::get('/pengajuan-cuti/{pengajuanCuti}', [PengajuanCutiController::class, 'show']);
    Route::put('/pengajuan-cuti/{pengajuanCuti}', [PengajuanCutiController::class, 'update']);
    Route::delete('/pengajuan-cuti/{pengajuanCuti}', [PengajuanCutiController::class, 'destroy']);

    // Approve HRD
    Route::middleware('role:hrd')->group(function () {
        Route::patch('/pengajuan-cuti/{pengajuanCuti}/approve-hrd', [PengajuanCutiController::class, 'approveHrd']);
    });

    // Approve Pimpinan
    Route::middleware('role:pimpinan')->group(function () {
        Route::patch('/pengajuan-cuti/{pengajuanCuti}/approve-pimpinan', [PengajuanCutiController::class, 'approvePimpinan']);
    });

    // Reject — HRD atau Pimpinan
    Route::middleware('role:hrd,pimpinan')->group(function () {
        Route::patch('/pengajuan-cuti/{pengajuanCuti}/reject', [PengajuanCutiController::class, 'reject']);
    });

    // Jenis Cuti — lihat: semua role | modif: hanya HRD
    Route::get('/jenis-cuti', [JenisCutiController::class, 'index']);
    Route::get('/jenis-cuti/{jenisCuti}', [JenisCutiController::class, 'show']);

    Route::middleware('role:hrd')->group(function () {
        Route::post('/jenis-cuti', [JenisCutiController::class, 'store']);
        Route::put('/jenis-cuti/{jenisCuti}', [JenisCutiController::class, 'update']);
        Route::delete('/jenis-cuti/{jenisCuti}', [JenisCutiController::class, 'destroy']);
    });

    Route::get('/jenis-izin', [JenisIzinController::class, 'index']);
    Route::get('/jenis-izin/{jenisIzin}', [JenisIzinController::class, 'show']);

    Route::middleware('role:hrd')->group(function () {
        Route::post('/jenis-izin', [JenisIzinController::class, 'store']);
        Route::put('/jenis-izin/{jenisIzin}', [JenisIzinController::class, 'update']);
        Route::delete('/jenis-izin/{jenisIzin}', [JenisIzinController::class, 'destroy']);
    });

    Route::middleware('role:hrd')->group(function () {
        Route::post('/kuota-cuti/generate', [KuotaCutiController::class, 'generate']);
    });

    Route::get('/kuota-cuti', [KuotaCutiController::class, 'index']);
    Route::get('/kuota-cuti/{kuotaCuti}', [KuotaCutiController::class, 'show']);

    Route::middleware('role:hrd')->group(function () {
        Route::post('/kuota-cuti', [KuotaCutiController::class, 'store']);
        Route::put('/kuota-cuti/{kuotaCuti}', [KuotaCutiController::class, 'update']);
        Route::delete('/kuota-cuti/{kuotaCuti}', [KuotaCutiController::class, 'destroy']);
    });

    Route::get('/pengajuan-izin', [PengajuanIzinController::class, 'index']);
    Route::post('/pengajuan-izin', [PengajuanIzinController::class, 'store']);
    Route::get('/pengajuan-izin/{pengajuanIzin}', [PengajuanIzinController::class, 'show']);
    Route::put('/pengajuan-izin/{pengajuanIzin}', [PengajuanIzinController::class, 'update']);
    Route::delete('/pengajuan-izin/{pengajuanIzin}', [PengajuanIzinController::class, 'destroy']);

    // Approve & Reject — HRD atau Pimpinan (1 level, langsung)
    Route::middleware('role:hrd,pimpinan')->group(function () {
        Route::patch('/pengajuan-izin/{pengajuanIzin}/approve', [PengajuanIzinController::class, 'approve']);
        Route::patch('/pengajuan-izin/{pengajuanIzin}/reject', [PengajuanIzinController::class, 'reject']);
    });

    // Profil pegawai sendiri (semua role)
    // ⚠️ HARUS di atas /pegawai/{pegawai} agar tidak dianggap ID
    Route::get('/pegawai/me', [PegawaiController::class, 'me']);

    // Lihat & edit — HRD/Pimpinan atau self
    Route::get('/pegawai/{pegawai}', [PegawaiController::class, 'show']);
    Route::put('/pegawai/{pegawai}', [PegawaiController::class, 'update']);

    // Khusus HRD
    Route::middleware('role:hrd')->group(function () {
        Route::get('/pegawai', [PegawaiController::class, 'index']);
        Route::post('/pegawai', [PegawaiController::class, 'store']);
        Route::delete('/pegawai/{pegawai}', [PegawaiController::class, 'destroy']);
    });

        Route::put('/me/profile', [ProfileController::class, 'update']);
    Route::put('/me/password', [ProfileController::class, 'updatePassword']);

    // ============================
    // MANAJEMEN PENGGUNA (khusus HRD)
    // ============================
    Route::middleware('role:hrd')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'index']);
        Route::post('/pengguna', [PenggunaController::class, 'store']);
        Route::get('/pengguna/{pengguna}', [PenggunaController::class, 'show']);
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update']);
        Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'destroy']);
        Route::patch('/pengguna/{pengguna}/toggle-aktif', [PenggunaController::class, 'toggleAktif']);
        Route::post('/pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPassword']);
    });

     Route::get('/riwayat-persetujuan', [RiwayatPersetujuanController::class, 'index']);
    Route::get('/riwayat-persetujuan/{riwayatPersetujuan}', [RiwayatPersetujuanController::class, 'show']);

    // Riwayat spesifik untuk satu pengajuan
    Route::get('/pengajuan-cuti/{pengajuanCuti}/riwayat', [RiwayatPersetujuanController::class, 'forCuti']);
    Route::get('/pengajuan-izin/{pengajuanIzin}/riwayat', [RiwayatPersetujuanController::class, 'forIzin']);
});