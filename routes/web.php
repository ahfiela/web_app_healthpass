<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InstansiQrController;

Route::get('/', function () {
    return view('welcome');
});

// ==========================================
// PORTAL KHUSUS WEB INSTANSI LUAR (QR GENERATOR)
// ==========================================
Route::prefix('instansi')->group(function () {
    Route::get('/qr-generator', [InstansiQrController::class, 'index']);
    Route::post('/qr-generator', [InstansiQrController::class, 'generate']);
});
Route::get('/rs/dashboard', function () { return view('rs.dashboard'); });
Route::get('/rs/visits', function () { return view('rs.visits'); });
Route::get('/rs/report', function () { return view('rs.report'); });

// Route Master Data Group
Route::get('/rs/master/doctors', function () { return view('rs.master.doctors'); });
Route::get('/rs/master/rooms', function () { return view('rs.master.rooms'); });
Route::get('/rs/master/diseases', function () { return view('rs.master.diseases'); });
Route::get('/rs/master/medications', function () { return view('rs.master.medications'); }); // Tambahan Baru