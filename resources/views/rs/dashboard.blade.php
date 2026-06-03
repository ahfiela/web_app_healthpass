@extends('rs.layout.app')

@section('content')
<div x-data="{ 
    stats: {}, 
    todayPatients: [],
    allPatients: [],

    initData() {
        // Ambil Statistik Ringkas
        fetch('/api/rs/dashboard/stats')
            .then(res => res.json())
            .then(data => this.stats = data || {});
            
        // Ambil Data Pasien Hari Ini
        fetch('/api/rs/visits/today')
            .then(res => res.json())
            .then(data => {
                this.todayPatients = Array.isArray(data) ? data : (data.data || []);
            });

        // Ambil Seluruh Data Pasien
        fetch('/api/rs/patients/all')
            .then(res => res.json())
            .then(data => {
                this.allPatients = Array.isArray(data) ? data : [];
            });
    }
}" x-init="initData(); setInterval(() => initData(), 5000);">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl border flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pasien Terregistrasi</p>
                <p class="text-3xl font-extrabold text-gray-800" x-text="stats.total_pasien ?? 0">0</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="bg-white p-6 rounded-xl border flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kunjungan Hari Ini</p>
                <p class="text-3xl font-extrabold text-gray-800" x-text="stats.kunjungan_hari_ini ?? 0">0</p>
            </div>
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center border"><i class="fa-solid fa-hospital-user"></i></div>
        </div>
        <div class="bg-white p-6 rounded-xl border flex items-center justify-between shadow-sm">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menunggu Validasi Edit</p>
                <p class="text-3xl font-extrabold text-amber-600" x-text="stats.pending_edits ?? 0">0</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center border"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-gray-50/50 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <h2 class="font-bold text-gray-800 text-sm">Daftar Pasien Berobat Hari Ini</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                            <th class="p-3">ID Visit</th>
                            <th class="p-3">No. BPJS</th>
                            <th class="p-3">Nama Pasien</th>
                            <th class="p-3">Status Layanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-gray-700">
                        <template x-if="todayPatients.length === 0">
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-400 text-xs">Tidak ada aktivitas kunjungan berobat hari ini.</td>
                            </tr>
                        </template>
                        <template x-for="p in todayPatients" :key="p.id">
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="p-3 font-mono font-bold text-blue-600" x-text="'#' + (p.queue_number || p.id || p.id_visit || '-')"></td>
                                <td class="p-3 font-mono" x-text="p.no_bpjs || '-'"></td>
                                <td class="p-3 font-semibold text-gray-900" x-text="p.user?.username || p.patient_name || 'Pasien Umum'"></td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase"
                                          :class="(p.status === 'completed' || p.status === 'approved') ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                                          x-text="p.status || 'pending'">
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-gray-50/50">
                <h2 class="font-bold text-gray-800 text-sm">Database Seluruh Pasien Terintegrasi (Aplikasi Mobile)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                            <th class="p-3">ID Pasien</th>
                            <th class="p-3">Nama Lengkap</th>
                            <th class="p-3">Email Pengguna</th>
                            <th class="p-3">Tanggal Bergabung</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-gray-700">
                        <template x-if="allPatients.length === 0">
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-400 text-xs">Belum ada data pasien terintegrasi di sistem b2b.</td>
                            </tr>
                        </template>
                        <template x-for="user in allPatients" :key="user.id">
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="p-3 font-mono" x-text="user.id || '-'"></td>
                                <td class="p-3 font-semibold text-gray-900" x-text="user.username || '-'"></td>
                                <td class="p-3 text-gray-500" x-text="user.email || '-'"></td>
                                <td class="p-3 text-xs text-gray-400" x-text="user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection