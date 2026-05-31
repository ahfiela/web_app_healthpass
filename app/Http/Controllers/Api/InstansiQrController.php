<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Disease, Disability};
use Illuminate\Http\Request;

class InstansiQrController extends Controller
{
    public function index()
    {
        $diseases = Disease::all();
        $disabilities = Disability::all(); 
        return view('instansi.qr-generator', compact('diseases', 'disabilities'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'required|string',
            'forbidden_icd_codes' => 'nullable|array',
            'forbidden_disabilities' => 'nullable|array',
        ]);

        // Menyusun payload aturan secara dinamis
        $payload = [
            'instansi' => $request->nama_instansi,
            'forbidden_icds' => $request->forbidden_icd_codes ?? [],
            'forbidden_disabilities' => $request->forbidden_disabilities ?? [],
            'created_at' => now()->toDateTimeString(),
        ];

        // Enkripsi payload aturan agar aman dan tidak bisa dimanipulasi
        $encryptedRules = encrypt(json_encode($payload));

        return back()->with([
            'qr_string' => $encryptedRules,
            'instansi' => $request->nama_instansi
        ]);
    }
}