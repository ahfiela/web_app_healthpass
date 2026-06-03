<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Appointment;
use App\Models\MedicationSchedule;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordEdit;
use App\Models\Disability;
use App\Models\Disease;
use App\Models\HealthProfile;
use App\Models\User;
use App\Models\HospitalAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FlutterController extends Controller
{
    // ENDPOINT 1: Mengambil list faskes murni dari database
    public function getHospitalList()
    {
        try {
            $hospitals = HospitalAdmin::select('id', 'kode_rs', 'nama_rs')->get();

            return response()->json([
                'success' => true,
                'data' => $hospitals
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data faskes resmi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ENDPOINT 2: Request pendaftaran kunjungan berobat
    public function requestVisit(Request $request)
    {
        $request->validate([
            'kode_rs' => 'required|string',
        ]);

        $user = Auth::user(); 
        if (!$user) {
            return response()->json(['message' => 'Token tidak valid atau kedaluwarsa'], 401);
        }

        $hospital = HospitalAdmin::where('kode_rs', $request->kode_rs)->first();
        
        // 🟢 FIX: Memastikan variabel nama cadangan aktif jika nama RS tidak ditemukan di database master
        $namaRsCadangan = 'Rumah Sakit Umum';
        $namaRsResmi = $hospital ? $hospital->nama_rs : $namaRsCadangan;

        try {
            $today = now()->toDateString();
            $queueNumber = Visit::where('kode_rs', $request->kode_rs)
                ->where('visit_date', $today)
                ->count() + 1;

            $visit = Visit::create([
                'no_bpjs'      => $user->no_bpjs,     
                'kode_rs'      => $request->kode_rs,  
                'rs_name'      => $namaRsResmi, 
                'visit_date'   => $today,
                'status'       => 'pending',
                'queue_number' => $queueNumber,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengirimkan permohonan kunjungan ke ' . $namaRsResmi,
                'data'    => $visit
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses data ke database.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ENDPOINT 3: Mengambil Data Dashboard Komplit untuk Flutter Pasien
    public function getDashboardData(Request $request)
    {
        $user = $request->user();
        $noBpjs = $user->no_bpjs;

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // 1. Ambil semua jadwal kontrol medis pasien
        $appointments = Appointment::where('no_bpjs', $noBpjs)
            ->orderBy('appointment_date')
            ->get();

        // 2. Ambil jadwal kontrol terdekat (hari ini ke depan)
        $nextAppointment = Appointment::where('no_bpjs', $noBpjs)
            ->whereDate('appointment_date', '>=', $today)
            ->orderBy('appointment_date')
            ->first();

        // 3. Ambil jadwal konsumsi obat yang aktif untuk hari ini
        $medicationsRaw = MedicationSchedule::where('no_bpjs', $noBpjs)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        // 🟢 FIX: Penanganan ekstraksi JSON days_of_week yang lebih aman dari duplikasi parsing
        $currentDayName = $now->format('l');
        $medications = $medicationsRaw->filter(function ($med) use ($currentDayName) {
            if (!$med->days_of_week) {
                return true;
            }
            
            $days = $med->days_of_week;
            if (is_string($days)) {
                $days = json_decode($days, true);
            }
            
            return is_array($days) ? in_array($currentDayName, $days) : true;
        })->values();

        // 4. Hitung alarm konsumsi obat berikutnya
        $currentTimeStr = $now->format('H:i:s');
        $nextMedication = $medications->where('remind_at', '>', $currentTimeStr)->sortBy('remind_at')->first() 
            ?? $medications->sortBy('remind_at')->first();

        // 5. Riwayat rekam medis lengkap pasien
        $history = MedicalRecord::with(['doctor', 'room', 'disease', 'visit', 'appointments', 'medicationSchedules'])
            ->where('no_bpjs', $noBpjs)
            ->orderByDesc('created_at')
            ->get();

        $latestRecord = $history->first();
        $healthProfile = HealthProfile::where('no_bpjs', $noBpjs)->first();
        $disabilities = $user->disabilities()->pluck('name')->toArray();
        $criticalDiseasesList = $user->diseases()->where('is_critical', true)->pluck('diseases.name')->toArray();
        $criticalDiseasesStr = !empty($criticalDiseasesList) ? implode(', ', $criticalDiseasesList) : 'Tidak ada';

        // 6. Penyusunan Struktur Paspor Kesehatan (Health Passport)
        $passport = [
            'patient_name'            => $user->username,
            'no_bpjs'                 => $noBpjs,
            'blood_type'              => $healthProfile?->blood_type ?? '-',
            'height_cm'               => $healthProfile?->height_cm,
            'weight_kg'               => $healthProfile?->weight_kg,
            'critical_diseases'       => $criticalDiseasesStr,
            'drug_allergies'          => $healthProfile?->drug_allergies ?? 'Tidak ada',
            'food_allergies'          => $healthProfile?->food_allergies ?? 'Tidak ada',
            'operation_history'       => $healthProfile?->operation_history ?? 'Tidak ada',
            'emergency_contact_name'  => $healthProfile?->emergency_contact_name ?? '-',
            'emergency_contact_phone' => $healthProfile?->emergency_contact_phone ?? '-',
            'disabilities'            => $disabilities ?? [],
            'last_diagnosis'          => $latestRecord?->disease?->name ?? 'Belum ada riwayat',
            'last_doctor'             => $latestRecord?->doctor?->name ?? '-',
            'last_room'               => $latestRecord?->room?->name ?? '-',
            'medical_status'          => $healthProfile?->health_status ?? 'sehat',
            'pending_approval_count'  => MedicalRecordEdit::whereHas('medicalRecord', function ($q) use ($noBpjs) {
                $q->where('no_bpjs', $noBpjs);
            })->where('status', 'pending')->count()
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'calendar_appointments' => $appointments,
                'next_appointment'      => $nextAppointment,
                'next_medication_alarm' => $nextMedication ? [
                    'medicine_name' => $nextMedication->medicine_name,
                    'rules'         => $nextMedication->rules,
                    'remind_at'     => $nextMedication->remind_at,
                ] : null,
                'today_medications'     => $medications,
                'medical_history'       => $history,
                'health_passport'       => $passport
            ]
        ]);
    }

    public function respondToEditRequest(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $edit = MedicalRecordEdit::with('medicalRecord')->findOrFail($id);

        if ($edit->medicalRecord->no_bpjs !== $request->user()->no_bpjs) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if ($request->status === 'approved') {
            $edit->medicalRecord->update($edit->proposed_changes);
        }

        $edit->update(['status' => $request->status]);

        return response()->json(['message' => 'Respon berhasil direkam']);
    }

    public function validatePassportQR(Request $request)
    {
        $request->validate([
            'qr_encrypted_string' => 'required|string'
        ]);

        try {
            $rules = json_decode(decrypt($request->qr_encrypted_string), true);
        } catch (\Exception $e) {
            // Fallback: try raw JSON directly if decryption fails
            $rules = json_decode($request->qr_encrypted_string, true);
            if (!$rules) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'QR tidak valid atau format tidak dikenali.'
                ], 400);
            }
        }

        $user = $request->user();
        
        // get active medical records
        $records = MedicalRecord::with('disease')
            ->where('no_bpjs', $user->no_bpjs)
            ->get();

        $activeDiseaseCodes = $records
            ->where('patient_status', '!=', 'sembuh-total')
            ->map(function ($record) {
                return $record->disease?->icd_code;
            })
            ->filter()
            ->toArray();

        // get active diseases
        $userDiseaseCodes = $user->diseases()->pluck('diseases.icd_code')->toArray();
        $allActiveDiseaseCodes = array_unique(array_merge($activeDiseaseCodes, $userDiseaseCodes));

        // get disabilities
        $userDisabilityIds = $user->disabilities()->pluck('disabilities.id')->toArray();

        $failed = false;
        $reasons = [];

        // Support both compressed and full keys
        $forbiddenIcds = $rules['icd'] ?? $rules['forbidden_icds'] ?? [];
        $forbiddenDisabilities = $rules['dis'] ?? $rules['forbidden_disabilities'] ?? [];

        // validate icd
        foreach ($forbiddenIcds as $icd) {
            if (in_array($icd, $allActiveDiseaseCodes)) {
                $failed = true;
                $disease = Disease::where('icd_code', $icd)->first();
                $diseaseName = $disease ? $disease->name : $icd;
                $reasons[] = "Penyakit aktif dilarang ditemukan: {$diseaseName} ({$icd})";
            }
        }

        // validate disabilities
        foreach ($forbiddenDisabilities as $disabilityId) {
            if (in_array($disabilityId, $userDisabilityIds)) {
                $failed = true;
                $disability = Disability::find($disabilityId);
                $disabilityName = $disability ? $disability->name : "Kekurangan fisik id: {$disabilityId}";
                $reasons[] = "Kekurangan fisik dilarang ditemukan: {$disabilityName}";
            }
        }

        if ($failed) {
            return response()->json([
                'status' => 'failed',
                'reasons' => $reasons
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lolos verifikasi'
        ]);
    }
    public function getProfile(Request $request)
    {
        // Mengambil data user yang sedang login berdasarkan token Sanctum
        $user = $request->user();
        $healthProfile = HealthProfile::where('no_bpjs', $user->no_bpjs)->first();

        return response()->json([
            'name' => $user->username,
            'no_bpjs' => $user->no_bpjs,
            'email' => $user->email,
            'born' => $user->born,
            'gender' => $user->gender,
            'emergency_contact_name' => $healthProfile?->emergency_contact_name ?? '',
            'emergency_contact_phone' => $healthProfile?->emergency_contact_phone ?? '',
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui.'
        ], 200);
    }
}