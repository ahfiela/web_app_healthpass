<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Doctor, Room, Disease, Visit, MedicalRecord, MedicalRecordEdit, Appointment, MedicationSchedule, User, Medication};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class RsWebController extends Controller
{
    // Fungsi Pembantu Multi-Tenant
    private function getKodeRs() { 
        return auth()->guard('hospital')->user()->kode_rs; 
    }

    // ==========================================
    // 1. MASTER DATA CRUD: DOKTER
    // ==========================================
    public function doctorIndex() { 
        return response()->json(Doctor::where('kode_rs', $this->getKodeRs())->get()); 
    }
    
    public function doctorStore(Request $request) {
        $kodeRs = $this->getKodeRs();
        $data = $request->validate([
            'nip' => ['required', Rule::unique('doctors', 'nip')->where('kode_rs', $kodeRs)], 
            'name' => 'required', 
            'specialist' => 'required'
        ]);
        
        $data['kode_rs'] = $kodeRs; 
        return response()->json(Doctor::create($data), 201);
    }

    public function doctorUpdate(Request $request, $id) {
        $kodeRs = $this->getKodeRs();
        $doctor = Doctor::where('id', $id)->where('kode_rs', $kodeRs)->firstOrFail();
        
        $data = $request->validate([
            'nip' => ['required', Rule::unique('doctors', 'nip')->where('kode_rs', $kodeRs)->ignore($id)],
            'name' => 'required',
            'specialist' => 'required'
        ]);
        
        $doctor->update($data);
        return response()->json(['message' => 'Data dokter berhasil diubah', 'data' => $doctor]);
    }

    public function doctorDestroy($id) {
        $doctor = Doctor::where('id', $id)->where('kode_rs', $this->getKodeRs())->firstOrFail();
        $doctor->delete();
        return response()->json(['message' => 'Dokter berhasil dihapus dari master data']);
    }

    // ==========================================
    // 2. MASTER DATA CRUD: RUANGAN / POLI
    // ==========================================
    public function roomIndex() { 
        return response()->json(Room::where('kode_rs', $this->getKodeRs())->get()); 
    }
    
    public function roomStore(Request $request) {
        $kodeRs = $this->getKodeRs();
        $data = $request->validate([
            'room_code' => ['required', Rule::unique('rooms', 'room_code')->where('kode_rs', $kodeRs)], 
            'name' => 'required'
        ]);
        
        $data['kode_rs'] = $kodeRs; 
        return response()->json(Room::create($data), 201);
    }

    public function roomUpdate(Request $request, $id) {
        $kodeRs = $this->getKodeRs();
        $room = Room::where('id', $id)->where('kode_rs', $kodeRs)->firstOrFail();
        
        $data = $request->validate([
            'room_code' => ['required', Rule::unique('rooms', 'room_code')->where('kode_rs', $kodeRs)->ignore($id)],
            'name' => 'required'
        ]);
        
        $room->update($data);
        return response()->json(['message' => 'Data ruangan berhasil diubah', 'data' => $room]);
    }

    public function roomDestroy($id) {
        $room = Room::where('id', $id)->where('kode_rs', $this->getKodeRs())->firstOrFail();
        $room->delete();
        return response()->json(['message' => 'Ruangan berhasil dihapus dari master data']);
    }

    // ==========================================
    // 3. MASTER DATA CRUD: PENYAKIT (ICD-10 Umum)
    // ==========================================
    public function diseaseIndex() { 
        return response()->json(Disease::all()); 
    }
    
    public function diseaseStore(Request $request) {
        $data = $request->validate([
            'icd_code' => 'required|unique:diseases,icd_code', 
            'name' => 'required', 
            'description' => 'nullable',
            'is_critical' => 'nullable|boolean'
        ]);
        return response()->json(Disease::create($data), 201);
    }

    public function diseaseUpdate(Request $request, $id) {
        $disease = Disease::findOrFail($id);
        $data = $request->validate([
            'icd_code' => 'required|unique:diseases,icd_code,' . $id, 
            'name' => 'required',
            'description' => 'nullable',
            'is_critical' => 'nullable|boolean'
        ]);
        $disease->update($data);
        return response()->json(['message' => 'Data penyakit berhasil diubah', 'data' => $disease]);
    }

    public function diseaseDestroy($id) {
        Disease::findOrFail($id)->delete();
        return response()->json(['message' => 'Penyakit berhasil dihapus']);
    }

    // ==========================================
    // 4. MASTER DATA CRUD: OBAT APOTEK
    // ==========================================
    public function getMedications() {
        return response()->json(Medication::where('kode_rs', $this->getKodeRs())->get());
    }

    public function storeMedication(Request $request) {
        $kodeRs = $this->getKodeRs();
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'stock' => 'required|integer'
        ]);
        
        $validated['kode_rs'] = $kodeRs; 
        $medication = Medication::create($validated);
        return response()->json(['message' => 'Obat berhasil disimpan!', 'data' => $medication], 201);
    }

    public function updateMedication(Request $request, $id) {
        $med = Medication::where('id', $id)->where('kode_rs', $this->getKodeRs())->firstOrFail();
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'stock' => 'required|integer'
        ]);
        $med->update($validated);
        return response()->json(['message' => 'Stok obat berhasil diubah', 'data' => $med]);
    }

    public function deleteMedication($id) {
        Medication::where('id', $id)->where('kode_rs', $this->getKodeRs())->firstOrFail()->delete(); 
        return response()->json(['message' => 'Obat berhasil dihapus dari master data']);
    }

    // ==========================================
    // 5. STATISTIK DASHBOARD
    // ==========================================
    public function getStats() {
        $kodeRs = $this->getKodeRs();
        return response()->json([
            'total_pasien' => User::count(),
            'kunjungan_hari_ini' => Visit::where('kode_rs', $kodeRs)->where('visit_date', now()->toDateString())->count(),
            // Menghitung edit request berdasarkan rekam medis -> kunjungan RS yang login
            'pending_edits' => MedicalRecordEdit::whereHas('medicalRecord.visit', function($q) use ($kodeRs) {
                $q->where('kode_rs', $kodeRs);
            })->where('status', 'pending')->count()
        ]);
    }

    // ==========================================
    // 6. OPERASIONAL MEDIS & SINKRONISASI JADWAL
    // ==========================================
    public function getPendingVisits() {
    // 🟢 FIX: Hilangkan 'completed' agar pasien yang sudah ditangani hilang dari daftar antrean hari ini
    return response()->json(
        Visit::where('kode_rs', $this->getKodeRs())
            ->whereIn('status', ['pending', 'approved']) 
            ->whereDate('visit_date', \Carbon\Carbon::today())
            ->orderBy('status', 'asc')
            ->get()
    );
}

    public function validateVisit(Request $request, $id) {
        $request->validate(['status' => 'required|in:approved,rejected']);
        Visit::where('kode_rs', $this->getKodeRs())->findOrFail($id)->update(['status' => $request->status]);
        return response()->json(['message' => 'Visit updated successfully']);
    }

    public function submitMedicalRecord(Request $request)
    {
        $request->validate([
            'visit_id'       => 'required',
            'no_bpjs'        => 'required|string|digits:13',
            'doctor_id'      => 'required',
            'room_id'        => 'required',
            'disease_id'     => 'required',
            'symptoms'       => 'required|string',
            'patient_status' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Selesaikan Status Kunjungan Antrean Rumah Sakit
            $visit = Visit::where('id', $request->visit_id)->where('kode_rs', $this->getKodeRs())->firstOrFail();
            $visit->update(['status' => 'completed']);

            // 2. Simpan Rekam Medis Utama
            // 🟢 SEKARANG AMAN: kode_rs dimasukkan dan model dengan $guarded=[] tidak akan memblokirnya
            MedicalRecord::create([
                'visit_id'       => $visit->id,
                'no_bpjs'        => $request->no_bpjs,
                'kode_rs'        => $this->getKodeRs(), // 🟢 Mengisi kolom kode_rs agar MySQL tidak error
                'doctor_id'      => $request->doctor_id,
                'room_id'        => $request->room_id,
                'disease_id'     => $request->disease_id,
                'symptoms'       => $request->symptoms,
                'patient_status' => $request->patient_status,
            ]);

            // 3. Update Profil Paspor Kesehatan Pasien
            $healthProfile = \App\Models\HealthProfile::firstOrCreate(
                ['no_bpjs' => $request->no_bpjs],
                ['blood_type' => 'O']
            );
            if ($request->filled('drug_allergies')) { $healthProfile->drug_allergies = $request->drug_allergies; }
            if ($request->filled('food_allergies')) { $healthProfile->food_allergies = $request->food_allergies; }
            $healthProfile->health_status = $request->patient_status === 'rawat-inap' ? 'darurat' : ($request->patient_status === 'rawat-jalan' ? 'perlu_pemantauan' : 'sehat');
            $healthProfile->save();

            // 4. Hubungkan Penyakit Kritis ke Pivot Table Pasien
            $disease = Disease::find($request->disease_id);
            $userPasien = User::where('no_bpjs', $request->no_bpjs)->first();
            if ($userPasien && $disease) {
                if ($disease->is_critical || stripos($disease->name, 'tuberculosis') !== false || stripos($disease->name, 'tbc') !== false) {
                    DB::table('user_disease')->updateOrInsert(
                        ['user_id' => $userPasien->id, 'disease_id' => $disease->id],
                        ['status' => 'perlu_pemantauan', 'notes' => 'Terdeteksi di ' . $this->getNamaRs(), 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // 5. PROSES PARSING JADWAL KONTROL BEROBAT
            if ($request->appointment_mode === 'custom' && $request->has('appointments')) {
                foreach ($request->appointments as $apt) {
                    if (!empty($apt['appointment_date'])) {
                        // 🟢 Menyertakan kode_rs dan rs_name ke tabel appointments
                        Appointment::create([
                            'no_bpjs'          => $request->no_bpjs,
                            'kode_rs'          => $this->getKodeRs(), // 🟢 Mengisi kode_rs
                            'rs_name'          => $visit->rs_name ?? $this->getNamaRs(),
                            'appointment_date' => $apt['appointment_date'],
                            'notes'            => $apt['notes'] ?? 'Kontrol Medis',
                        ]);
                    }
                }
            } elseif ($request->appointment_mode === 'routine' && !empty($request->routine_start_date)) {
                $startDate = Carbon::parse($request->routine_start_date);
                $duration = intval($request->routine_duration ?? 1);
                $selectedDaysInput = $request->selected_days ?? []; 

                $dayMapping = [
                    1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                    4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
                ];
                
                $daysFilter = [];
                foreach ($selectedDaysInput as $dayNum) {
                    if (isset($dayMapping[$dayNum])) { $daysFilter[] = $dayMapping[$dayNum]; }
                }

                $endDate = $request->routine_type === 'weekly' 
                    ? $startDate->copy()->addWeeks($duration) 
                    : $startDate->copy()->addMonths($duration);

                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    if (in_array($date->format('l'), $daysFilter)) {
                        // 🟢 Menyertakan kode_rs dan rs_name ke tabel appointments rutin
                        Appointment::create([
                            'no_bpjs'          => $request->no_bpjs,
                            'kode_rs'          => $this->getKodeRs(), // 🟢 Mengisi kode_rs
                            'rs_name'          => $visit->rs_name ?? $this->getNamaRs(),
                            'appointment_date' => $date->toDateString(),
                            'notes'            => $request->routine_notes ?? 'Kontrol Terjadwal',
                        ]);
                    }
                }
            }

            // 6. PROSES SIMPAN ALARM MINUM OBAT
            if ($request->has('medications')) {
                foreach ($request->medications as $med) {
                    if (!empty($med['medicine_name'])) {
                        $startStr = now()->toDateString();
                        $endStr = now()->addDays(intval($med['duration_days'] ?? 1) - 1)->toDateString();
                        $allEnglishDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

                        MedicationSchedule::create([
                            'no_bpjs'       => $request->no_bpjs,
                            'medicine_name' => $med['medicine_name'],
                            'rules'         => $med['rules'],
                            'remind_at'     => $med['remind_at'],
                            'start_date'    => $startStr,
                            'end_date'      => $endStr,
                            'days_of_week'  => json_encode($allEnglishDays),
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Laporan & Pengingat Sukses Disinkronisasikan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Kendala pemrosesan server: ' . $e->getMessage()], 500);
        }
    }

    public function requestEditReport(Request $request, $id) {
        $request->validate(['proposed_changes' => 'required|array']);
        MedicalRecordEdit::create([
            'medical_record_id' => $id,
            'proposed_changes' => $request->proposed_changes,
            'status' => 'pending'
        ]);
        return response()->json(['message' => 'Permintaan perbaikan dikirim ke pasien']);
    }

    public function getAllPatientsUntukWeb() {
        return response()->json(User::orderBy('created_at', 'desc')->get());
    }

    public function getHistoryUntukWeb() { 
        // 🟢 FIX: Dikembalikan menggunakan whereHas('visit') karena tabel medical_records tidak punya kode_rs langsung
        return response()->json(
            MedicalRecord::with(['doctor', 'room', 'disease'])
                ->whereHas('visit', function($query) {
                    $query->where('kode_rs', $this->getKodeRs());
                })
                ->orderBy('created_at', 'desc')
                ->get()
        ); 
    }
}