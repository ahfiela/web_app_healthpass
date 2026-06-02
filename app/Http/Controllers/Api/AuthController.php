<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'no_bpjs'  => 'required|numeric|digits:13|unique:users,no_bpjs',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->post('https://bpjs-api-production.up.railway.app/api/bpjs/validate', [
                    'no_bpjs' => $request->no_bpjs
                ]);

            // Jika server validasi memberikan respon 404 (Nomor tidak terdaftar)
            if ($response->status() === 404) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nomor BPJS tidak ditemukan atau tidak terdaftar di sistem pusat.'
                ], 404);
            }

            // Jika error HTTP selain 404 (misal server down / 500)
            if ($response->failed()) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Gagal terhubung ke server verifikasi BPJS.'
                ], 502);
            }

            if ($response->json('status') !== 'aktif') {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'BPJS tidak aktif atau tidak terdaftar.'
                ], 400);
            }

            $bpjsData = $response->json('data');

            $user = User::create([
                'username' => $bpjsData['username'] ?? 'Pasien Tanpa Nama', 
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'no_bpjs'  => $request->no_bpjs,
                'born'     => $bpjsData['born'] ?? now()->format('Y-m-d'),
                'gender'   => $bpjsData['gender'] ?? 'male', 
            ]);

            return response()->json([
                'status' => 'success',
                'token'  => $user->createToken('flutter_token')->plainTextToken
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi gangguan internal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
{
    $loginInput = $request->input('login');

    if (!$loginInput || !$request->password) {
        return response()->json([
            'status' => 'error',
            'message' => 'Nomor BPJS / Email / Username dan password wajib diisi.'
        ], 422);
    }

    $user = User::where('email', $loginInput)
        ->orWhere('no_bpjs', $loginInput)
        ->orWhere('username', $loginInput)
        ->first();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Nama, Nomor BPJS / Email tidak ditemukan.'
        ], 404);
    }

    if (!Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Password yang dimasukkan salah.'
        ], 401);
    }

    return response()->json([
        'status' => 'success',
        'token' => $user->createToken('flutter_token')->plainTextToken,
        'username' => $user->username,
    ]);
}
}