<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FlutterController;
use App\Http\Controllers\Api\RsWebController;
use App\Http\Controllers\Api\QrScannerController;

// Auth Publik untuk Pasien Flutter
Route::post('/flutter/register', [AuthController::class, 'register']);
Route::post('/flutter/login', [AuthController::class, 'login']);

// ==========================================
// ENDPOINT FLUTTER PASIEN (PROTECTED SANCTUM)
// ==========================================
Route::middleware('auth:sanctum')->prefix('pasien')->group(function () {
    Route::post('/visit/request', [FlutterController::class, 'requestVisit']);
    Route::get('/dashboard', [FlutterController::class, 'getDashboardData']);
    Route::post('/edit-requests/{id}/respond', [FlutterController::class, 'respondToEditRequest']);
    Route::get('/hospitals', [FlutterController::class, 'getHospitalList']);
    
    // Jalur Flutter untuk verifikasi QR Passport
    Route::post('/qr/verify-scan', [FlutterController::class, 'validatePassportQR']);
    Route::get('/profile', [FlutterController::class, 'getProfile']);
    Route::post('/profile/update', [FlutterController::class, 'updateProfile']);
});