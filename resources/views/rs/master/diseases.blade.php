@extends('rs.layout.app')

@section('content')
<div x-data="{ 
    items: [], showModal: false, isEdit: false,
    searchQuery: '',
    suggestions: [],
    // REVISI: Tambahkan properti is_critical di dalam form state
    form: { id: '', icd_code: '', name: '', description: '', is_critical: false },

    worldDiseases: [
        {icd_code: 'A09', name: 'Gastroenteritis and colitis of infectious origin', desc: 'Diare infeksius akut'},
        {icd_code: 'A15', name: 'Respiratory tuberculosis', desc: 'TBC Paru aktif'},
        {icd_code: 'B20', name: 'Human immunodeficiency virus [HIV] disease', desc: 'Infeksi HIV/AIDS'},
        {icd_code: 'E11', name: 'Type 2 diabetes mellitus', desc: 'Kencing manis / DM Tipe 2'},
        {icd_code: 'I10', name: 'Essential (primary) hypertension', desc: 'Tekanan darah tinggi / Hipertensi'},
        {icd_code: 'J00', name: 'Acute nasopharyngitis [common cold]', desc: 'Flu / Batuk Pilek biasa'},
        {icd_code: 'K29', name: 'Gastritis and duodenitis', desc: 'Sakit maag lambung akut'},
        {icd_code: 'N39', name: 'Urinary tract infection', desc: 'Infeksi Saluran Kemih / ISK'}
    ],

    initData() { 
        fetch('/api/rs/diseases').then(res => res.json()).then(data => this.items = data); 
    },
    openCreate() { 
        this.isEdit = false; 
        this.searchQuery = '';
        this.form = { id: '', icd_code: '', name: '', description: '', is_critical: false }; 
        this.showModal = true; 
    },
    // REVISI: Tambahkan fungsi openEdit jika suatu saat ingin mengubah status penyakit yang ada
    openEdit(item) {
        this.isEdit = true;
        this.searchQuery = item.icd_code + ' - ' + item.name;
        this.form = { 
            id: item.id, 
            icd_code: item.icd_code, 
            name: item.name, 
            description: item.description,
            is_critical: item.is_critical == 1 ? true : false 
        };
        this.showModal = true;
    },
    updateSearch() {
        if (this.searchQuery.length < 2) { this.suggestions = []; return; }
        this.suggestions = this.worldDiseases.filter(d => 
            d.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
            d.icd_code.toLowerCase().includes(this.searchQuery.toLowerCase())
        ).slice(0, 5);
    },
    selectSuggestion(disease) {
        this.form.icd_code = disease.icd_code;
        this.form.name = disease.name;
        this.form.description = disease.desc;
        this.form.is_critical = false; // default awal
        this.searchQuery = disease.icd_code + ' - ' + disease.name;
        this.suggestions = [];
    },
    submitForm() {
        if(!this.form.icd_code) { alert('Wajib memilih penyakit dari daftar standar dunia!'); return; }
        let url = this.isEdit ? '/api/rs/diseases/' + this.form.id : '/api/rs/diseases';
        let method = this.isEdit ? 'PUT' : 'POST';
        fetch(url, { 
            method: method, 
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, 
            body: JSON.stringify(this.form) 
        }).then(() => { this.showModal = false; this.initData(); });
    },
    deleteItem(id) { 
        if(confirm('Hapus penyakit ini?')) { 
            fetch('/api/rs/diseases/' + id, { 
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => this.initData()); 
        } 
    }
}" x-init="initData()">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-virus-covid text-blue-600"></i> Master Diagnosa Penyakit (ICD-10)</h2>
        <button @click="openCreate()" class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-sm"><i class="fa-solid fa-plus"></i> Tambah Penyakit</button>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                    <th class="p-3">Kode ICD-10</th>
                    <th class="p-3">Nama Resmi Global</th>
                    <th class="p-3">Keterangan</th>
                    <th class="p-3">Kategori</th> <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y text-gray-700">
                <template x-if="items.length === 0">
                    <tr><td colspan="5" class="p-4 text-center text-gray-400 text-xs">Belum ada data penyakit.</td></tr>
                </template>
                <template x-for="item in items" :key="item.id">
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-3 font-mono font-bold text-red-600" x-text="item.icd_code"></td>
                        <td class="p-3 font-semibold text-gray-900" x-text="item.name"></td>
                        <td class="p-3 text-gray-500" x-text="item.description"></td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase"
                                  :class="item.is_critical == 1 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'"
                                  x-text="item.is_critical == 1 ? 'Kritis (Passport)' : 'Penyakit Biasa'">
                            </span>
                        </td>
                        <td class="p-3 flex justify-center gap-2">
                            <button @click="openEdit(item)" class="text-blue-600 text-xs font-semibold px-2"><i class="fa-solid fa-pen"></i> Edit</button>
                            <button @click="deleteItem(item.id)" class="text-red-600 text-xs font-semibold px-2"><i class="fa-solid fa-trash"></i> Hapus</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div x-show="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50" x-transition style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border relative" @click.away="showModal = false">
            <h3 class="text-md font-bold text-gray-800 mb-4" x-text="isEdit ? 'Edit Data Diagnosa Master' : 'Validasi Penyakit Standar WHO'"></h3>
            <form @submit.prevent="submitForm()" class="space-y-4">
                <div class="relative">
                    <label class="block text-xs font-bold text-gray-500 mb-1">CARI NAMA / KODE PENYAKIT</label>
                    <input type="text" x-model="searchQuery" :disabled="isEdit" @input="updateSearch()" class="w-full border p-2.5 rounded-lg text-sm disabled:bg-gray-50" placeholder="Ketik kata kunci...">
                    
                    <div x-show="suggestions.length > 0" class="absolute left-0 right-0 bg-white border mt-1 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto divide-y text-xs">
                        <template x-for="disease in suggestions">
                            <div @click="selectSuggestion(disease)" class="p-2.5 hover:bg-blue-50 cursor-pointer transition">
                                <span class="font-bold text-red-600 font-mono" x-text="disease.icd_code"></span> - <span class="font-semibold text-gray-800" x-text="disease.name"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.is_critical" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block text-xs font-bold text-gray-800">Tandai Sebagai Penyakit Kritis</span>
                            <span class="block text-[11px] text-gray-500 mt-0.5">Jika dicentang, rekam medis pasien dengan penyakit ini otomatis dikirim ke Health Passport bagian bawah.</span>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end gap-2 text-xs font-bold">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-400">Batal</button>
                    <button type="submit" :disabled="!form.icd_code" :class="form.icd_code ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400'" class="px-5 py-2 rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection