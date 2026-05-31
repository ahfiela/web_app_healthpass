<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RsWebController;
use App\Http\Controllers\Api\FlutterController;

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
    
    // Jalur Flutter untuk mengirimkan teks hasil scan kamera ke server
    Route::post('/qr/verify-scan', [FlutterController::class, 'validatePassportQR']);
});

// ==========================================
// ENDPOINT WEB RUMAH SAKIT (B2B DIRECT)
// ==========================================
Route::prefix('rs')->group(function () {
    Route::get('/dashboard/stats', [RsWebController::class, 'getStats']);

    // TAMBAHAN: Jalur khusus Web RS untuk memuat data tanpa token Sanctum Pasien
    Route::get('/patients/all', [RsWebController::class, 'getAllPatientsUntukWeb']);
    Route::get('/medical-records/history', [RsWebController::class, 'getHistoryUntukWeb']);

    Route::get('/visits/pending', [RsWebController::class, 'getPendingVisits']);
    Route::post('/visits/{id}/validate', [RsWebController::class, 'validateVisit']);
    Route::post('/medical-records/submit', [RsWebController::class, 'submitMedicalRecord']);

    // Route Master Dokter
    Route::get('/doctors', [RsWebController::class, 'doctorIndex']);
    Route::post('/doctors', [RsWebController::class, 'doctorStore']);
    Route::put('/doctors/{id}', [RsWebController::class, 'doctorUpdate']);
    Route::delete('/doctors/{id}', [RsWebController::class, 'doctorDestroy']);

    // Route Master Ruangan
    Route::get('/rooms', [RsWebController::class, 'roomIndex']);
    Route::post('/rooms', [RsWebController::class, 'roomStore']);
    Route::put('/rooms/{id}', [RsWebController::class, 'roomUpdate']);
    Route::delete('/rooms/{id}', [RsWebController::class, 'roomDestroy']);

    // Route Master Penyakit
    Route::get('/diseases', [RsWebController::class, 'diseaseIndex']);
    Route::post('/diseases', [RsWebController::class, 'diseaseStore']);
    Route::put('/diseases/{id}', [RsWebController::class, 'diseaseUpdate']);
    Route::delete('/diseases/{id}', [RsWebController::class, 'diseaseDestroy']);

    // Route Master Obat
    Route::get('/medications', [RsWebController::class, 'getMedications']);
    Route::post('/medications', [RsWebController::class, 'storeMedication']);
    Route::put('/medications/{id}', [RsWebController::class, 'updateMedication']);
    Route::delete('/medications/{id}', [RsWebController::class, 'deleteMedication']);
});