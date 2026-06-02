<?php

namespace App\Http\Controllers;

use App\Models\HospitalAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class HospitalAuthController extends Controller
{
    // Tampilan Form Login Admin RS
    public function showLogin() 
    {
        return view('auth.hospital.login');
    }

    // Proses Login Admin RS
    public function login(Request $request) 
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Autentikasi menggunakan guard khusus 'hospital'
        if (Auth::guard('hospital')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/rs/dashboard');
        }

        return back()->withErrors([
            'email' => 'Kredensial login Admin RS tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    // Tampilan Form Registrasi Tenant RS
    public function showRegister() 
    {
        return view('auth.hospital.register');
    }

    // Proses Registrasi Tenant RS (Hit Validasi ke Server Railway)
    public function register(Request $request) 
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:hospital_admins',
            'password' => 'required|string|min:6|confirmed',
            'kode_rs' => 'required|string',
        ]);

        // LINK SERVER VALIDASI PUSAT DI RAILWAY
        $urlValidasi = "https://rs-api-production-74cf.up.railway.app/api/v1/verify-hospital"; 

        try {
            // Hit API menggunakan HTTP Client bawaan Laravel
            $response = Http::post($urlValidasi, [
                'kode_rs' => $request->kode_rs
            ]);

            $result = $response->json();

            // Validasi apakah response dari Railway sukses atau gagal
            if ($response->failed() || !isset($result['status']) || $result['status'] !== 'success') {
                return back()->withErrors([
                    'kode_rs' => $result['message'] ?? 'Kode RS tidak terdaftar atau dinonaktifkan di server pusat Railway.'
                ])->withInput();
            }

            // Jika sukses, buat data Admin Tenant baru di database lokal
            // Nama RS diambil secara dinamis dari properti 'resource' milik server pusat
            $admin = HospitalAdmin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'kode_rs' => strtoupper($request->kode_rs),
                'nama_rs' => $result['resource']['nama_rs'], 
            ]);

            // Auto-login ke session guard hospital
            Auth::guard('hospital')->login($admin);
            
            return redirect('/rs/dashboard');

        } catch (\Exception $e) {
            // Menangkap jika ada kendala jaringan (misal server Railway down)
            return back()->withErrors([
                'kode_rs' => 'Gagal verifikasi. Koneksi ke server pusat Railway terputus.'
            ])->withInput();
        }
    }

    // Proses Logout Admin RS
    public function logout(Request $request) 
    {
        Auth::guard('hospital')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('hospital.login');
    }
}