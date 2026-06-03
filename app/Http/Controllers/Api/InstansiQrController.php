<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disease;
use App\Models\Disability;
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
            'use_bypass' => 'nullable',
        ]);

        // build compressed payload
        $payload = [
            'ins' => $request->nama_instansi,
            'icd' => $request->forbidden_icd_codes ?? [],
            'dis' => $request->forbidden_disabilities ?? [],
            'cat' => now()->toDateTimeString(),
        ];

        // check if bypass (unencrypted) is requested
        if ($request->has('use_bypass')) {
            $qrString = json_encode($payload);
        } else {
            $qrString = encrypt(json_encode($payload));
        }

        return back()->with([
            'qr_string' => $qrString,
            'instansi' => $request->nama_instansi
        ]);
    }
}
