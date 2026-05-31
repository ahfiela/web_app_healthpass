@extends('rs.layout.app')

@section('content')
<div x-data="{ 
    pendingVisits: [],
    historyRecords: [],
    showReportModal: false,
    doctors: [], rooms: [], diseases: [],
    
    form: {
        visit_id: '', no_bpjs: '', doctor_id: '', room_id: '', disease_id: '',
        symptoms: '', patient_status: 'rawat-jalan', medications: [], appointments: []
    },

    initData() {
        fetch('/api/rs/visits/pending')
            .then(res => res.json())
            .then(data => {
                this.pendingVisits = Array.isArray(data) ? data : (data.data || []);
            });

        fetch('/api/rs/doctors').then(res => res.json()).then(data => this.doctors = data || []);
        fetch('/api/rs/rooms').then(res => res.json()).then(data => this.rooms = data || []);
        fetch('/api/rs/diseases').then(res => res.json()).then(data => this.diseases = data || []);
        
        fetch('/api/pasien/dashboard')
            .then(res => res.json())
            .then(res => {
                this.historyRecords = res.data?.medical_history || res.medical_history || [];
            });
    },

    openCreateReport(visit) {
        this.form.visit_id = visit.id || visit.id_visit;
        this.form.no_bpjs = visit.no_bpjs;
        this.form.doctor_id = '';
        this.form.room_id = '';
        this.form.disease_id = '';
        this.form.symptoms = '';
        this.form.patient_status = 'rawat-jalan';
        this.form.medications = [];
        this.form.appointments = [];
        this.showReportModal = true;
    },

    submitForm() {
        fetch('/api/rs/medical-records/submit', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(this.form)
        }).then(res => res.json()).then(data => {
            alert(data.message || 'Laporan medis berhasil dikirim dan disinkronisasi!');
            this.showReportModal = false;
            this.initData();
        });
    }
}" x-init="initData()" class="space-y-8">

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                <i class="fa-solid fa-bell text-blue-600"></i> Antrean Request Kedatangan Berobat Pasien
            </h2>
            <span class="bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full" 
                  x-text="(pendingVisits ? pendingVisits.length : 0) + ' Pasien Antre'">
            </span>
        </div>
        
        <div class="p-2">
            <template x-if="!pendingVisits || pendingVisits.length === 0">
                <div class="text-center py-10 text-gray-400 text-xs">
                    <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i> Tidak ada request kedatangan aktif saat ini.
                </div>
            </template>
            
            <template x-if="pendingVisits && pendingVisits.length > 0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50 border-b">
                                <th class="p-3">ID Visit</th>
                                <th class="p-3">No. BPJS Pasien</th>
                                <th class="p-3">Tanggal Datang</th>
                                <th class="p-3 text-center">Aksi Operasional</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-gray-700">
                            <template x-for="visit in pendingVisits" :key="visit.id">
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="p-3 font-bold text-blue-600" x-text="'#'+(visit.id || visit.id_visit)"></td>
                                    <td class="p-3 font-mono font-semibold" x-text="visit.no_bpjs || '-'"></td>
                                    <td class="p-3 text-gray-500" x-text="visit.visit_date || '-'"></td>
                                    <td class="p-3 flex justify-center gap-2">
                                        <template x-if="visit.status === 'pending' || !visit.status">
                                            <button @click="
                                                fetch('/api/rs/visits/' + (visit.id || visit.id_visit) + '/validate', {
                                                    method: 'POST',
                                                    headers: {'Content-Type': 'application/json'},
                                                    body: JSON.stringify({status: 'approved'})
                                                }).then(() => initData())
                                            " class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition shadow-sm">
                                                <i class="fa-solid fa-square-check"></i> Terima Pasien
                                            </button>
                                        </template>
                                        
                                        <template x-if="visit.status === 'approved'">
                                            <button @click="openCreateReport(visit)" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 transition shadow-sm">
                                                <i class="fa-solid fa-file-medical"></i> Buat Laporan Medis
                                            </button>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b bg-gray-50/50">
            <h2 class="font-bold text-gray-800 text-sm"><i class="fa-solid fa-clock-rotate-left text-gray-500"></i> Log Arsip Riwayat Penanganan Laporan Medis Pasien</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                        <th class="p-3">NO. BPJS</th>
                        <th class="p-3">USERNAME</th>
                        <th class="p-3">DOKTER</th>
                        <th class="p-3">DIAGNOSA</th>
                        <th class="p-3">STATUS AKHIR</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-gray-700">
                    <template x-if="!historyRecords || historyRecords.length === 0">
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-400 text-xs">Belum ada riwayat arsip rekam medis yang dikirim.</td>
                        </tr>
                    </template>
                    <template x-for="item in historyRecords" :key="item.id">
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-3 font-mono" x-text="item.no_bpjs || '-'"></td>
                            <td class="p-3 font-semibold text-gray-900" x-text="item.username || item.user?.name || '-'"></td>
                            <td class="p-3" x-text="item.doctor?.name || item.doctor_name || 'Dokter Umum'"></td>
                            <td class="p-3 font-mono text-red-600" x-text="item.disease?.icd_code || item.icd_code || '-'"></td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold" 
                                      :class="{
                                          'bg-green-100 text-green-700': item.patient_status === 'sembuh-total',
                                          'bg-blue-100 text-blue-700': item.patient_status === 'rawat-jalan',
                                          'bg-orange-100 text-orange-700': item.patient_status === 'rawat-inap'
                                      }"
                                      x-text="item.patient_status || 'rawat-jalan'">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection