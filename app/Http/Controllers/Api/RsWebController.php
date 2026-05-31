<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// REVISI: Pastikan Medication sudah dimasukkan ke dalam daftar Models
use App\Models\{Doctor, Room, Disease, Visit, MedicalRecord, MedicalRecordEdit, Appointment, MedicationSchedule, User, Medication};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RsWebController extends Controller
{
    // ==========================================
    // 1. MASTER DATA CRUD: DOKTER (Gelar dr. di Frontend)
    // ==========================================
    public function doctorIndex() { 
        return response()->json(Doctor::all()); 
    }
    
    public function doctorStore(Request $request) {
        $data = $request->validate([
            'nip' => 'required|unique:doctors,nip', 
            'name' => 'required', 
            'specialist' => 'required'
        ]);
        return response()->json(Doctor::create($data), 201);
    }

    // TAMBAHAN: Fungsi Update Dokter
    public function doctorUpdate(Request $request, $id) {
        $doctor = Doctor::findOrFail($id);
        $data = $request->validate([
            'nip' => 'required|unique:doctors,nip,' . $id, // Ignore id milik dokter ini sendiri saat validasi
            'name' => 'required',
            'specialist' => 'required'
        ]);
        $doctor->update($data);
        return response()->json(['message' => 'Data dokter berhasil diubah', 'data' => $doctor]);
    }

    // TAMBAHAN: Fungsi Delete Dokter
    public function doctorDestroy($id) {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();
        return response()->json(['message' => 'Dokter berhasil dihapus dari master data']);
    }


    // ==========================================
    // 2. MASTER DATA CRUD: RUANGAN / POLI
    // ==========================================
    public function roomIndex() { 
        return response()->json(Room::all()); 
    }
    
    public function roomStore(Request $request) {
        $data = $request->validate([
            'room_code' => 'required|unique:rooms,room_code', 
            'name' => 'required'
        ]);
        return response()->json(Room::create($data), 201);
    }

    // TAMBAHAN: Fungsi Update Ruangan
    public function roomUpdate(Request $request, $id) {
        $room = Room::findOrFail($id);
        $data = $request->validate([
            'room_code' => 'required|unique:rooms,room_code,' . $id,
            'name' => 'required'
        ]);
        $room->update($data);
        return response()->json(['message' => 'Data ruangan berhasil diubah', 'data' => $room]);
    }

    // TAMBAHAN: Fungsi Delete Ruangan
    public function roomDestroy($id) {
        $room = Room::findOrFail($id);
        $room->delete();
        return response()->json(['message' => 'Ruangan berhasil dihapus dari master data']);
    }


    // ==========================================
    // 3. MASTER DATA CRUD: PENYAKIT (ICD-10)
    // ==========================================
    public function diseaseIndex() { 
        return response()->json(Disease::all()); 
    }
    
    public function diseaseStore(Request $request) {
        $data = $request->validate([
            'icd_code' => 'required|unique:diseases,icd_code', 
            'name' => 'required', 
            'description' => 'nullable'
        ]);
        return response()->json(Disease::create($data), 201);
    }

    // TAMBAHAN: Fungsi Update Penyakit
    public function diseaseUpdate(Request $request, $id) {
        $disease = Disease::findOrFail($id);
        $data = $request->validate([
            'icd_code' => 'required|unique:diseases,icd_code,' . $id,
            'name' => 'required',
            'description' => 'nullable'
        ]);
        $disease->update($data);
        return response()->json(['message' => 'Data penyakit berhasil diubah', 'data' => $disease]);
    }

    // TAMBAHAN: Fungsi Delete Penyakit
    public function diseaseDestroy($id) {
        $disease = Disease::findOrFail($id);
        $disease->delete();
        return response()->json(['message' => 'Penyakit berhasil dihapus dari master data']);
    }


    // ==========================================
    // 4. MASTER DATA CRUD: OBAT APOTEK (WHO STANDAR)
    // ==========================================
    public function getMedications() {
        return response()->json(Medication::all());
    }

    public function storeMedication(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'stock' => 'required|integer'
        ]);
        $medication = Medication::create($validated);
        return response()->json(['message' => 'Obat berhasil disimpan!', 'data' => $medication], 201);
    }

    // TAMBAHAN: Fungsi Update Obat
    public function updateMedication(Request $request, $id) {
        $med = Medication::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'stock' => 'required|integer'
        ]);
        $med->update($validated);
        return response()->json(['message' => 'Stok obat berhasil diubah', 'data' => $med]);
    }

    public function deleteMedication($id) {
        $med = Medication::findOrFail($id);
        $med->delete(); 
        return response()->json(['message' => 'Obat berhasil dihapus dari master data']);
    }


    // ==========================================
    // 5. STATISTIK DASHBOARD
    // ==========================================
    public function getStats() {
        return response()->json([
            'total_pasien' => User::count(),
            'kunjungan_hari_ini' => Visit::where('visit_date', now()->toDateString())->count(),
            'pending_edits' => MedicalRecordEdit::where('status', 'pending')->count()
        ]);
    }


    // ==========================================
    // 6. OPERASIONAL MEDIS & SINKRONISASI JADWAL
    // ==========================================
    public function getPendingVisits() { 
        return response()->json(Visit::where('status', 'pending')->get()); 
    }

    public function validateVisit(Request $request, $id) {
        $request->validate(['status' => 'required|in:approved,rejected']);
        Visit::findOrFail($id)->update(['status' => $request->status]);
        return response()->json(['message' => 'Visit updated successfully']);
    }

    public function submitMedicalRecord(Request $request)
    {
        $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'no_bpjs' => 'required|string|digits:13',
            'doctor_id' => 'required|exists:doctors,id',
            'room_id' => 'required|exists:rooms,id',
            'disease_id' => 'required|exists:diseases,id',
            'symptoms' => 'required|string',
            'patient_status' => 'required|in:sembuh-total,rawat-jalan,rawat-inap',
            'appointments' => 'nullable|array',
            'medications' => 'nullable|array'
        ]);

        DB::beginTransaction();
        try {
            $visit = Visit::findOrFail($request->visit_id);
            $visit->update(['status' => 'completed']);

            MedicalRecord::create([
                'visit_id' => $visit->id,
                'no_bpjs' => $request->no_bpjs,
                'doctor_id' => $request->doctor_id,
                'room_id' => $request->room_id,
                'disease_id' => $request->disease_id,
                'symptoms' => $request->symptoms,
                'patient_status' => $request->patient_status,
            ]);

            if ($request->has('appointments')) {
                foreach ($request->appointments as $apt) {
                    if ($apt['type'] === 'spesifik') {
                        foreach ($apt['specific_dates'] as $date) {
                            Appointment::create([
                                'no_bpjs' => $request->no_bpjs,
                                'rs_name' => $visit->rs_name,
                                'appointment_date' => Carbon::parse($date)->toDateString(),
                                'notes' => $apt['notes'],
                            ]);
                        }
                    } else {
                        $start = Carbon::parse($apt['start_date']);
                        $end = Carbon::parse($apt['end_date']);
                        while ($start->lte($end)) {
                            Appointment::create([
                                'no_bpjs' => $request->no_bpjs,
                                'rs_name' => $visit->rs_name,
                                'appointment_date' => $start->toDateString(),
                                'notes' => $apt['notes'],
                            ]);
                            $start->addWeeks($apt['interval_weeks']);
                        }
                    }
                }
            }

            if ($request->has('medications')) {
                foreach ($request->medications as $med) {
                    MedicationSchedule::create([
                        'no_bpjs' => $request->no_bpjs,
                        'medicine_name' => $med['medicine_name'],
                        'rules' => $med['rules'],
                        'remind_at' => $med['remind_at'],
                        'start_date' => $med['start_date'],
                        'end_date' => $med['end_date'],
                        'days_of_week' => $med['days_of_week'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Laporan sukses diproses!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
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
    // Mengembalikan data seluruh user terdaftar untuk tabel database di dashboard web
    return response()->json(User::orderBy('created_at', 'desc')->get());
}

public function getHistoryUntukWeb() {
    // Mengembalikan data riwayat lengkap beserta relasinya untuk tabel log di menu visits web
    return response()->json(MedicalRecord::with(['doctor', 'room', 'disease'])->orderBy('created_at', 'desc')->get());
}
}