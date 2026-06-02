<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\HealthProfile;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\DB;

class QrScannerController extends Controller
{
    /**
     * Menganalisis kiriman hasil scan QR Code Passport dari Aplikasi Flutter Pasien.
     */
    public function analyzePassportQr(Request $request)
    {
        $request->validate([
            'qr_content' => 'required|string|digits:13' 
        ]);

        $noBpjs = $request->qr_content;

        // 1. Tarik profile data identitas pasien
        $user = User::where('no_bpjs', $noBpjs)->first();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Data Paspor Kesehatan Tidak Dikenali / QR Code Tidak Valid.'
            ], 404);
        }

        // 2. Ambil Rekam Profil Fisik Terkini
        $profile = HealthProfile::where('no_bpjs', $noBpjs)->first();

        // 3. Tarik semua daftar penyakit aktif/kritis dari tabel pivot
        $criticalDiseases = DB::table('user_disease')
            ->join('diseases', 'user_disease.disease_id', '=', 'diseases.id')
            ->where('user_disease.user_id', $user->id)
            ->select('diseases.icd_code', 'diseases.name', 'user_disease.status', 'user_disease.notes')
            ->get();

        // 4. Tarik Riwayat Kunjungan Medis Terakhir Pasien
        // 🟢 PERBAIKAN: Menghilangkan duplikasi penulisan variable penarikan record yang ganda
        $lastRecord = MedicalRecord::with(['disease'])
            ->where('no_bpjs', $noBpjs)
            ->orderBy('created_at', 'desc')
            ->first();

        // 5. Kembalikan Response Hasil Analisis Komprehensif
        return response()->json([
            'status' => 'success',
            'message' => 'Analisis Paspor Kesehatan Berhasil!',
            'data' => [
                'identitas' => [
                    'nama_pasien' => $user->username,
                    'no_bpjs' => $user->no_bpjs,
                    'email' => $user->email,
                    'gender' => $user->gender ?? 'Tidak Diisi'
                ],
                'kondisi_fisik' => [
                    'golongan_darah' => $profile->blood_type ?? 'Belum Diisi',
                    'tinggi_badan' => ($profile->height_cm ?? '-') . ' cm',
                    'berat_badan' => ($profile->weight_kg ?? '-') . ' kg',
                    'status_kesehatan_umum' => $profile->health_status ?? 'sehat'
                ],
                'keamanan_medis' => [
                    'alergi_obat' => $profile->drug_allergies ?? 'Tidak Ada',
                    'alergi_makanan' => $profile->food_allergies ?? 'Tidak Ada',
                ],
                'penyakit_kritis_aktif' => $criticalDiseases, 
                'diagnosa_terakhir_rs' => $lastRecord && $lastRecord->disease ? '[' . $lastRecord->disease->icd_code . '] ' . $lastRecord->disease->name : 'Belum memiliki riwayat penanganan'
            ]
        ], 200);
    }
}