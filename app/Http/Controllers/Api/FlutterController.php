<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Visit, Appointment, MedicationSchedule, MedicalRecord, MedicalRecordEdit, Disability};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FlutterController extends Controller
{
    public function requestVisit(Request $request)
    {
        $request->validate(['rs_name' => 'required|string']);
        Visit::create([
            'no_bpjs' => $request->user()->no_bpjs,
            'rs_name' => $request->rs_name,
            'visit_date' => now()->toDateString(),
            'status' => 'pending'
        ]);
        return response()->json(['message' => 'Sukses request berobat! Antre di loket RS.']);
    }

    public function getDashboardData(Request $request)
    {
        $noBpjs = $request->user()->no_bpjs;
        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $timeStr = $now->toTimeString();

        // 1. Data Kalender Berobat
        $appointments = Appointment::where('no_bpjs', $noBpjs)->get();

        // 2. Data Jadwal Minum Obat Selanjutnya + Validasi Hari Spesifik
        $allMedications = MedicationSchedule::where('no_bpjs', $noBpjs)
            ->where('start_date', '<=', $todayStr)
            ->where('end_date', '>=', $todayStr)
            ->get()
            ->filter(function($item) use ($now) {
                if ($item->days_of_week) {
                    return in_array($now->format('l'), $item->days_of_week);
                }
                return true;
            });

        // Cari Obat Terdekat Selanjutnya
        $nextMedication = $allMedications->where('remind_at', '>', $timeStr)
            ->sortBy('remind_at')
            ->first();

        // Jika hari ini sudah habis, ambil jadwal obat paling pagi untuk besok
        if (!$nextMedication) {
            $nextMedication = $allMedications->sortBy('remind_at')->first();
        }

        // 3. Riwayat Berobat Komprehensif
        $history = MedicalRecord::with(['doctor', 'room', 'disease'])
            ->where('no_bpjs', $noBpjs)
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Passport Kesehatan (Ringkasan Kondisi Terakhir)
        $latest = $history->first();
        $passport = [
            'no_bpjs' => $noBpjs,
            'patient_name' => $request->user()->username,
            'last_diagnosis' => $latest ? $latest->disease->name : 'Tidak ada',
            'last_doctor' => $latest ? $latest->doctor->name : 'Tidak ada',
            'last_room' => $latest ? $latest->room->name : 'Tidak ada',
            'medical_status' => $latest ? $latest->patient_status : 'Sehat',
            'pending_approval_count' => MedicalRecordEdit::whereHas('medicalRecord', function($q) use ($noBpjs) {
                $q->where('no_bpjs', $noBpjs);
            })->where('status', 'pending')->count()
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'calendar_appointments' => $appointments,
                'next_medication_alarm' => $nextMedication ? [
                    'medicine_name' => $nextMedication->medicine_name,
                    'rules' => $nextMedication->rules,
                    'remind_at' => $nextMedication->remind_at,
                    'trigger_notification' => true
                ] : null,
                'medical_history' => $history,
                'health_passport' => $passport
            ]
        ]);
    }

    public function respondToEditRequest(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);
        $edit = MedicalRecordEdit::with('medicalRecord')->findOrFail($id);

        if ($edit->medicalRecord->no_bpjs !== $request->user()->no_bpjs) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if ($request->status === 'approved') {
            $edit->medicalRecord->update($edit->proposed_changes);
        }
        
        $edit->update(['status' => $request->status]);
        return response()->json(['message' => 'Respon berhasil direkam!']);
    }

    // FUNGSI VALIDASI SCAN QR PASPOR DARI INSTANSI
    public function validatePassportQR(Request $request)
    {
        $request->validate([
            'qr_encrypted_string' => 'required|string', 
        ]);

        // 1. Ekstrak isi kriteria dari QR Code Web Instansi
        try {
            $decryptedRules = json_decode(decrypt($request->qr_encrypted_string), true);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'QR Code kedaluwarsa, salah, atau tidak dikenali oleh sistem.'
            ], 400);
        }

        // 2. Ambil data pasien langsung dari Token Sanctum aktif
        $user = $request->user();
        $noBpjs = $user->no_bpjs;

        // 3. Ambil rekam medis berjalan milik pasien
        $latestRecords = MedicalRecord::where('no_bpjs', $noBpjs)->with('disease')->get();
        $activeDiseasesCodes = $latestRecords->where('patient_status', '!=', 'sembuh-total')
            ->pluck('disease.icd_code')
            ->toArray();

        $isFailed = false;
        $reasons = [];

        // --- PROSES COCOKKAN ATURAN QR INSTANSI ---

        // A. Validasi Riwayat Penyakit Klinis Aktif (ICD)
        if (!empty($decryptedRules['forbidden_icds']) && is_array($decryptedRules['forbidden_icds'])) {
            foreach ($decryptedRules['forbidden_icds'] as $code) {
                if (in_array($code, $activeDiseasesCodes)) {
                    $isFailed = true;
                    $diseaseData = $latestRecords->where('disease.icd_code', $code)->first();
                    $reasons[] = "Pasien terdeteksi memiliki riwayat penyakit klinis aktif: " . ($diseaseData->disease->name ?? $code);
                }
            }
        }

        // B. Validasi Kriteria Kekurangan / Kelainan Fisik (Dinamis)
        if (!empty($decryptedRules['forbidden_disabilities']) && is_array($decryptedRules['forbidden_disabilities'])) {
            $userDisabilities = DB::table('user_disability')
                ->where('user_id', $user->id)
                ->pluck('disability_id')
                ->toArray();

            foreach ($decryptedRules['forbidden_disabilities'] as $disabilityId) {
                if (in_array($disabilityId, $userDisabilities)) {
                    $isFailed = true;
                    $disabilityName = Disability::find($disabilityId)->name ?? 'Kelainan Fisik';
                    $reasons[] = "Pasien terdeteksi memiliki kondisi kekurangan: " . $disabilityName;
                }
            }
        }

        // 4. Output Response Hasil Verifikasi untuk Aplikasi Flutter
        if ($isFailed) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Verifikasi Gagal! Anda tidak memenuhi kriteria sehat ' . $decryptedRules['instansi'],
                'reasons' => $reasons,
                'instansi' => $decryptedRules['instansi']
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Verifikasi Sukses! Anda lolos kriteria sehat ' . $decryptedRules['instansi'],
            'instansi' => $decryptedRules['instansi']
        ], 200);
    }
}