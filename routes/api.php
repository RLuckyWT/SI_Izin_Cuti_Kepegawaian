<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PengajuanCutiController;
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
});