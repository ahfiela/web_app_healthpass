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

        // Simulasi Tembak Server BPJS Pusat untuk mengambil data autentik
        $response = Http::post('https://server-bpjs-inti.railway.internal/api/bpjs/validate', [
            'no_bpjs' => $request->no_bpjs
        ]);

        if ($response->failed() || $response->json('status') !== 'aktif') {
            return response()->json(['status' => 'error', 'message' => 'BPJS tidak aktif atau tidak terdaftar.'], 400);
        }

        $bpjsData = $response->json('data');

        $user = User::create([
            'username' => $bpjsData['username'], 
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'no_bpjs'  => $request->no_bpjs,
            'born'     => $bpjsData['born'],
            'gender'   => $bpjsData['gender'],
        ]);

        return response()->json([
            'status' => 'success',
            'token'  => $user->createToken('flutter_token')->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->login)
                    ->orWhere('no_bpjs', $request->login)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Kredensial salah.'], 401);
        }

        return response()->json([
            'status' => 'success',
            'token'  => $user->createToken('flutter_token')->plainTextToken,
            'username' => $user->username
        ], 200);
    }
}