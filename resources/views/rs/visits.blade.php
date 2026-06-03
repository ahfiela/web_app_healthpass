@extends('rs.layout.app')

@section('content')
<div x-data="visitsPage" x-init="initData()" class="space-y-6 p-4">

    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="p-4 bg-gray-50 border-b">
            <h2 class="font-bold text-gray-800 text-sm"><i class="fa-solid fa-user-clock text-blue-600"></i> Antrean Pasien Hari Ini</h2>
        </div>
        <div class="p-2">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                        <th class="p-3">ID Kunjungan</th>
                        <th class="p-3">Nomor BPJS</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="visit in pendingVisits" :key="visit.id">
                        <tr class="border-b hover:bg-gray-50/50">
                            <td class="p-3 font-bold text-blue-600" x-text="'#'+(visit.queue_number || visit.id)"></td>
                            <td class="p-3 font-mono text-xs" x-text="visit.no_bpjs"></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                      :class="{
                                          'bg-amber-100 text-amber-700': (visit.status || '').toLowerCase() === 'pending',
                                          'bg-blue-100 text-blue-700': (visit.status || '').toLowerCase() === 'approved',
                                          'bg-green-100 text-green-700': (visit.status || '').toLowerCase() === 'completed'
                                      }" x-text="visit.status">
                                </span>
                            </td>
                            <td class="p-3 flex justify-center">
                                <template x-if="['approved', 'pending'].includes((visit.status || '').toLowerCase())">
                                    <button @click="openCreateReport(visit)" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded-lg transition-colors">
                                        Tangani Pasien
                                    </button>
                                </template>
                                <template x-if="(visit.status || '').toLowerCase() === 'completed'">
                                    <span class="text-xs text-gray-400 font-medium"><i class="fa-solid fa-circle-check text-green-500"></i> Selesai Ditangani</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="showReportModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-xl max-w-3xl w-full max-h-[92vh] overflow-y-auto p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-2">
                <h3 class="font-bold text-gray-800 text-sm">Input Rekam Medis Pasien</h3>
                <button @click="showReportModal = false" class="text-gray-400 text-xl font-bold">&times;</button>
            </div>

            <form @submit.prevent="submitForm()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Diagnosa Utama (ICD-10) *</label>
                    <select x-model="form.disease_id" required class="w-full border rounded-lg p-2 text-xs bg-white">
                        <option value="">-- Pilih Penyakit --</option>
                        <template x-for="dis in diseases" :key="dis.id">
                            <option :value="dis.id" x-text="'[' + dis.icd_code + '] ' + dis.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Gejala Klinis *</label>
                    <textarea x-model="form.symptoms" required class="w-full border rounded-lg p-2 text-xs" rows="2" placeholder="Keluhan pasien..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" x-model="form.height_cm" class="w-full border rounded-lg p-2 text-xs" placeholder="Contoh: 170">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Berat Badan (kg)</label>
                        <input type="number" step="0.1" x-model="form.weight_kg" class="w-full border rounded-lg p-2 text-xs" placeholder="Contoh: 60">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Alergi Obat</label>
                        <textarea x-model="form.drug_allergies" class="w-full border rounded-lg p-2 text-xs" rows="2" placeholder="Sebutkan alergi obat jika ada..."></textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Alergi Makanan</label>
                        <textarea x-model="form.food_allergies" class="w-full border rounded-lg p-2 text-xs" rows="2" placeholder="Sebutkan alergi makanan jika ada..."></textarea>
                    </div>
                </div>

                <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-emerald-800"><i class="fa-solid fa-pills"></i> Resep Obat (Bisa isi lebih dari satu obat)</span>
                        <button type="button" @click="addMedicationRow()" class="bg-emerald-600 text-white px-2 py-1 rounded text-[10px] font-bold">+ Tambah Obat</button>
                    </div>
                    
                    <div class="space-y-2">
                        <template x-for="(medRow, index) in form.medications" :key="index">
                            <div class="grid grid-cols-12 gap-2 bg-white p-2 rounded-lg border items-end">
                                <div class="col-span-4">
                                    <label class="block text-gray-500 mb-0.5 text-[10px]">Nama Obat</label>
                                    <select x-model="medRow.medication_id" required class="w-full border rounded p-1.5 bg-white text-[11px]">
                                        <option value="">-- Pilih Obat --</option>
                                        <template x-for="m in medicationsMaster" :key="m.id">
                                            <option :value="m.id" x-text="m.name + ' ('+m.type+')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-gray-500 mb-0.5 text-[10px]">Aturan Minum</label>
                                    <input type="text" x-model="medRow.rules" required placeholder="3 x 1 Tablet" class="w-full border rounded p-1.5 text-[11px]">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-gray-500 mb-0.5 text-[10px]">Jam Alarm</label>
                                    <input type="time" x-model="medRow.remind_at" required class="w-full border rounded p-1 text-[11px]">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-gray-500 mb-0.5 text-[10px]">Durasi (Hari)</label>
                                    <input type="number" min="1" x-model="medRow.duration_days" required class="w-full border rounded p-1 text-[11px]">
                                </div>
                                <div class="col-span-1 text-center">
                                    <button type="button" @click="removeMedicationRow(index)" class="text-red-500 text-lg font-bold mb-1">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-blue-50/40 p-4 rounded-xl border border-blue-100 space-y-3">
                    <span class="font-bold text-blue-800 block"><i class="fa-solid fa-calendar-days"></i> Atur Jadwal Kontrol Kembali Pasien</span>
                    
                    <div class="flex gap-4 border-b pb-2">
                        <label class="flex items-center gap-1 font-medium text-gray-700 cursor-pointer">
                            <input type="radio" value="custom" x-model="appointment_mode"> Sekali / Beberapa Kali Datang (Pilih Tanggal Manual)
                        </label>
                        <label class="flex items-center gap-1 font-medium text-gray-700 cursor-pointer">
                            <input type="radio" value="routine" x-model="appointment_mode"> Rutin Berjadwal Otomatis
                        </label>
                    </div>

                    <div x-show="appointment_mode === 'custom'" class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 text-[11px]">Masukkan tanggal kunjungan kembali pasien:</span>
                            <button type="button" @click="addAppointmentRow()" class="bg-blue-600 text-white px-2 py-0.5 rounded text-[10px] font-bold">+ Tambah Baris Tanggal</button>
                        </div>
                        <template x-for="(aptRow, idx) in form.custom_appointments" :key="idx">
                            <div class="flex gap-2 bg-white p-2 rounded-lg border items-center">
                                <div class="w-5/12">
                                    <input type="date" x-model="aptRow.appointment_date" class="w-full border rounded p-1.5 text-[11px]">
                                </div>
                                <div class="w-6/12">
                                    <input type="text" x-model="aptRow.notes" placeholder="Catatan (Misal: Cek Laboratorium / Lepas Jahitan)" class="w-full border rounded p-1.5 text-[11px]">
                                </div>
                                <div class="w-1/12 text-center">
                                    <button type="button" @click="removeAppointmentRow(idx)" class="text-red-500 font-bold text-base">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="appointment_mode === 'routine'" class="space-y-3 bg-white p-3 rounded-lg border" x-transition>
                        <div class="flex gap-4 items-center bg-gray-50 p-2 rounded-md">
                            <span class="font-bold text-gray-600">Pilih Kategori:</span>
                            <label class="flex items-center gap-1"><input type="radio" value="weekly" x-model="routine_type"> Per Minggu</label>
                            <label class="flex items-center gap-1"><input type="radio" value="monthly" x-model="routine_type"> Per Bulan</label>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-gray-600 mb-0.5">Tanggal Mulai Rutinitas</label>
                                <input type="date" x-model="form.routine_start_date" class="w-full border rounded p-1.5 text-[11px]">
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-0.5" x-text="routine_type === 'weekly' ? 'Durasi (Berapa Minggu)' : 'Durasi (Berapa Bulan)'"></label>
                                <input type="number" min="1" x-model="form.routine_duration" class="w-full border rounded p-1.5 text-[11px]">
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-0.5">Catatan Rutinitas</label>
                                <input type="text" x-model="form.routine_notes" class="w-full border rounded p-1.5 text-[11px]">
                            </div>
                        </div>

                        <div class="pt-2 border-t">
                            <span class="block font-bold text-gray-600 mb-1">Rutin Kontrol Setiap Hari Berikut:</span>
                            <div class="flex flex-wrap gap-3 bg-gray-50 p-2 rounded-lg">
                                <label class="flex items-center gap-1"><input type="checkbox" value="1" x-model="selected_days"> Senin</label>
                                <label class="flex items-center gap-1"><input type="checkbox" value="2" x-model="selected_days"> Selasa</label>
                                <label class="flex items-center gap-1"><input type="checkbox" value="3" x-model="selected_days"> Rabu</label>
                                <label class="flex items-center gap-1"><input type="checkbox" value="4" x-model="selected_days"> Kamis</label>
                                <label class="flex items-center gap-1"><input type="checkbox" value="5" x-model="selected_days"> Jumat</label>
                                <label class="flex items-center gap-1"><input type="checkbox" value="6" x-model="selected_days"> Sabtu</label>
                                <label class="flex items-center gap-1"><input type="checkbox" value="7" x-model="selected_days"> Minggu</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold text-gray-700 mb-0.5">Dokter Penanggung Jawab *</label>
                        <select x-model="form.doctor_id" required class="w-full border rounded-lg p-2 bg-white text-xs">
                            <option value="">-- Pilih Dokter --</option>
                            <template x-for="doc in doctors" :key="doc.id">
                                <option :value="doc.id" x-text="doc.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-0.5">Ruangan / Poli Klinik *</label>
                        <select x-model="form.room_id" required class="w-full border rounded-lg p-2 bg-white text-xs">
                            <option value="">-- Pilih Ruangan --</option>
                            <template x-for="rm in rooms" :key="rm.id">
                                <option :value="rm.id" x-text="rm.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-0.5">Status Akhir Pasien *</label>
                        <select x-model="form.patient_status" required class="w-full border rounded-lg p-2 bg-white text-xs">
                            <option value="rawat-jalan">Rawat Jalan</option>
                            <option value="sembuh-total">Sembuh Total</option>
                            <option value="rawat-inap">Rawat Inap</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="showReportModal = false" class="bg-gray-100 font-bold text-gray-600 px-4 py-2 rounded-lg">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white font-bold px-5 py-2 rounded-lg shadow">Simpan & Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('visitsPage', () => ({
            pendingVisits: [],
            historyRecords: [],
            showReportModal: false,
            doctors: [], rooms: [], diseases: [], medicationsMaster: [],
            
            appointment_mode: 'custom', 
            routine_type: 'weekly',    
            selected_days: [],          

            form: {
                visit_id: '', 
                no_bpjs: '', 
                doctor_id: '', 
                room_id: '', 
                disease_id: '',
                symptoms: '', 
                patient_status: 'rawat-jalan',
                drug_allergies: '',
                food_allergies: '',
                height_cm: '',
                weight_kg: '',
                
                custom_appointments: [
                    { appointment_date: '', notes: '' }
                ],

                routine_start_date: '',
                routine_duration: 1, 
                routine_notes: 'Kontrol Rutin',

                medications: [
                    { medication_id: '', rules: '', remind_at: '07:00', duration_days: 5 }
                ]
            },

            initData() {
                this.fetchPending();
                fetch('/api/rs/doctors').then(res => res.json()).then(data => this.doctors = data || []);
                fetch('/api/rs/rooms').then(res => res.json()).then(data => this.rooms = data || []);
                fetch('/api/rs/diseases').then(res => res.json()).then(data => this.diseases = data || []);
                fetch('/api/rs/medications').then(res => res.json()).then(data => this.medicationsMaster = data || []);
                fetch('/api/rs/medical-records/history').then(res => res.json()).then(data => this.historyRecords = data || []);
                
                setInterval(() => {
                    this.fetchPending();
                    fetch('/api/rs/medical-records/history').then(res => res.json()).then(data => this.historyRecords = data || []);
                }, 3000);
            },

            fetchPending() {
                fetch('/api/rs/visits/pending')
                    .then(res => res.json())
                    .then(data => {
                        const incoming = data || [];
                        if (this.pendingVisits.length > 0 && incoming.length > this.pendingVisits.length) {
                            const diff = incoming.filter(v => !this.pendingVisits.some(p => p.id === v.id));
                            diff.forEach(v => {
                                showToast(
                                    'Antrean Pasien Baru',
                                    `Pasien BPJS: ${v.no_bpjs} telah masuk antrean!`,
                                    'success'
                                );
                            });
                        }
                        this.pendingVisits = incoming;
                    });
            },

            openCreateReport(visit) {
                this.form.visit_id = visit.id;
                this.form.no_bpjs = visit.no_bpjs;
                this.form.doctor_id = ''; this.form.room_id = ''; this.form.disease_id = ''; this.form.symptoms = '';
                this.form.custom_appointments = [{ appointment_date: '', notes: '' }];
                this.form.medications = [{ medication_id: '', rules: '', remind_at: '07:00', duration_days: 5 }];
                this.form.routine_start_date = '';
                this.form.routine_duration = 1;
                this.selected_days = [];
                this.appointment_mode = 'custom';
                
                if (visit.user && visit.user.health_profile) {
                    this.form.height_cm = visit.user.health_profile.height_cm || '';
                    this.form.weight_kg = visit.user.health_profile.weight_kg || '';
                    this.form.drug_allergies = visit.user.health_profile.drug_allergies || '';
                    this.form.food_allergies = visit.user.health_profile.food_allergies || '';
                } else {
                    this.form.height_cm = '';
                    this.form.weight_kg = '';
                    this.form.drug_allergies = '';
                    this.form.food_allergies = '';
                }
                
                this.showReportModal = true;
            },

            addMedicationRow() {
                this.form.medications.push({ medication_id: '', rules: '', remind_at: '07:00', duration_days: 5 });
            },

            removeMedicationRow(index) {
                if (this.form.medications.length > 1) this.form.medications.splice(index, 1);
            },

            addAppointmentRow() {
                this.form.custom_appointments.push({ appointment_date: '', notes: '' });
            },

            removeAppointmentRow(index) {
                if (this.form.custom_appointments.length > 1) this.form.custom_appointments.splice(index, 1);
            },

            submitForm() {
                const payload = {
                    visit_id: this.form.visit_id,
                    no_bpjs: this.form.no_bpjs,
                    doctor_id: this.form.doctor_id,
                    room_id: this.form.room_id,
                    disease_id: this.form.disease_id,
                    symptoms: this.form.symptoms,
                    patient_status: this.form.patient_status,
                    drug_allergies: this.form.drug_allergies,
                    food_allergies: this.form.food_allergies,
                    height_cm: this.form.height_cm,
                    weight_kg: this.form.weight_kg,
                    appointment_mode: this.appointment_mode,
                    routine_type: this.routine_type,
                    selected_days: this.selected_days,
                    routine_start_date: this.form.routine_start_date,
                    routine_duration: parseInt(this.form.routine_duration),
                    routine_notes: this.form.routine_notes,
                    appointments: [], 
                    medications: []
                };

                if (this.appointment_mode === 'custom') {
                    this.form.custom_appointments.forEach(apt => {
                        if (apt.appointment_date) payload.appointments.push(apt);
                    });
                }

                this.form.medications.forEach(med => {
                    let master = this.medicationsMaster.find(m => m.id == med.medication_id);
                    if (master && med.rules) {
                        payload.medications.push({
                            medicine_name: master.name,
                            rules: med.rules,
                            remind_at: med.remind_at,
                            duration_days: parseInt(med.duration_days)
                        });
                    }
                });

                fetch('/api/rs/medical-records/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => {
                    return res.json().then(data => {
                        if (!res.ok) {
                            let errorMsg = data.error || data.message || 'Terjadi kesalahan pada server';
                            if (data.errors) {
                                errorMsg += '\n' + Object.values(data.errors).flat().join('\n');
                            }
                            throw new Error(errorMsg);
                        }
                        return data;
                    });
                })
                .then(data => {
                    alert('Data berhasil disimpan dan disinkronisasikan ke Flutter!');
                    this.showReportModal = false;
                    this.initData();
                })
                .catch(err => {
                    alert('Gagal: ' + err.message);
                });
            }
        }));
    });
</script>
@endsection