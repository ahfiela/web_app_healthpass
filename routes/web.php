<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InstansiQrController;
use App\Http\Controllers\HospitalAuthController; 
use App\Http\Controllers\Api\RsWebController;

Route::get('/', function () {
    return redirect()->route('hospital.login');
});

Route::get('/login', function () {
    return redirect()->route('hospital.login');
})->name('login');

Route::prefix('instansi')->group(function () {
    Route::get('/qr-generator', [InstansiQrController::class, 'index']);
    Route::post('/qr-generator', [InstansiQrController::class, 'generate']);
});


// ========================================================
// AUTHENTICATION TENANT RS (GUEST ACCESS)
// ========================================================
Route::middleware('guest:hospital')->group(function () {
    Route::get('/rs/login', [HospitalAuthController::class, 'showLogin'])->name('hospital.login');
    Route::post('/rs/login', [HospitalAuthController::class, 'login'])->name('hospital.login.post');
    Route::get('/rs/register', [HospitalAuthController::class, 'showRegister'])->name('hospital.register');
    Route::post('/rs/register', [HospitalAuthController::class, 'register'])->name('hospital.register.post');
});

// =========================
// AREA INTERN DASHBOARD RS
// =========================
Route::middleware('auth:hospital')->group(function () {
    // 1. Halaman Tampilan Blade
    Route::get('/rs/dashboard', function () { return view('rs.dashboard'); })->name('rs.dashboard');
    Route::get('/rs/visits', function () { return view('rs.visits'); });
    Route::get('/rs/report', function () { return view('rs.report'); });

    Route::get('/rs/master/doctors', function () { return view('rs.master.doctors'); });
    Route::get('/rs/master/rooms', function () { return view('rs.master.rooms'); });
    Route::get('/rs/master/diseases', function () { return view('rs.master.diseases'); });
    Route::get('/rs/master/medications', function () { return view('rs.master.medications'); });
    
    // Aksi Logout
    Route::post('/rs/logout', [HospitalAuthController::class, 'logout'])->name('hospital.logout');

    // 2. Jalur API Internal Dashboard
    Route::prefix('api/rs')->group(function () {
        Route::get('/dashboard/stats', [RsWebController::class, 'getStats']);
        Route::get('/patients/all', [RsWebController::class, 'getAllPatientsUntukWeb']);
        Route::get('/medical-records/history', [RsWebController::class, 'getHistoryUntukWeb']);
        Route::get('/visits/pending', [RsWebController::class, 'getPendingVisits']);
        Route::get('/visits/today', [RsWebController::class, 'getTodayVisits']);
        Route::post('/visits/{id}/validate', [RsWebController::class, 'validateVisit']);
        
Route::post('/medical-records/submit', [RsWebController::class, 'submitMedicalRecord']);

        // Master Dokter
        Route::get('/doctors', [RsWebController::class, 'doctorIndex']);
        Route::post('/doctors', [RsWebController::class, 'doctorStore']);
        Route::put('/doctors/{id}', [RsWebController::class, 'doctorUpdate']);
        Route::delete('/doctors/{id}', [RsWebController::class, 'doctorDestroy']);

        // Master Ruangan
        Route::get('/rooms', [RsWebController::class, 'roomIndex']);
        Route::post('/rooms', [RsWebController::class, 'roomStore']);
        Route::put('/rooms/{id}', [RsWebController::class, 'roomUpdate']);
        Route::delete('/rooms/{id}', [RsWebController::class, 'roomDestroy']);

        // Master Penyakit
        Route::get('/diseases', [RsWebController::class, 'diseaseIndex']);
        Route::post('/diseases', [RsWebController::class, 'diseaseStore']);
        Route::put('/diseases/{id}', [RsWebController::class, 'diseaseUpdate']);
        Route::delete('/diseases/{id}', [RsWebController::class, 'diseaseDestroy']);

        // Master Obat
        Route::get('/medications', [RsWebController::class, 'getMedications']);
        Route::post('/medications', [RsWebController::class, 'storeMedication']);
        Route::put('/medications/{id}', [RsWebController::class, 'updateMedication']);
        Route::delete('/medications/{id}', [RsWebController::class, 'deleteMedication']);
    });
});