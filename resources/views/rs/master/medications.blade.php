@extends('rs.layout.app')

@section('content')
<div x-data="{ 
    items: [], 
    showModal: false,
    searchQuery: '',
    suggestions: [],
    form: { id: '', name: '', type: '', stock: 100 },

    worldMedications: [
        {name: 'Paracetamol', type: 'Analgesik / Antipiretik'},
        {name: 'Amoxicillin', type: 'Antibiotik Penisilin'},
        {name: 'Metformin', type: 'Antidiabetes Oral'},
        {name: 'Amlodipine', type: 'Antishipertensi'},
        {name: 'Atorvastatin', type: 'Antikolesterol'},
        {name: 'Omeprazole', type: 'Antiasam Lambung'},
        {name: 'Ibuprofen', type: 'Anti-inflamasi (NSAID)'},
        {name: 'Cetirizine', type: 'Antihistamin / Alergi'},
        {name: 'Salbutamol', type: 'Inhaler Asma'},
        {name: 'Loperamide', type: 'Antidiare'}
    ],

    initData() { 
        // Mengambil data real dari database lewat API controller
        fetch('/api/rs/medications')
            .then(res => res.json())
            .then(data => this.items = data)
            .catch(err => console.error('Gagal memuat data obat. Pastikan API & Database sudah siap:', err));
    },
    openCreate() { 
        this.searchQuery = ''; 
        this.form = { id: '', name: '', type: '', stock: 100 }; 
        this.showModal = true; 
    },
    updateSearch() {
        if (this.searchQuery.length < 2) { this.suggestions = []; return; }
        this.suggestions = this.worldMedications.filter(m => 
            m.name.toLowerCase().includes(this.searchQuery.toLowerCase())
        ).slice(0, 5);
    },
    selectSuggestion(med) {
        this.form.name = med.name;
        this.form.type = med.type;
        this.searchQuery = med.name;
        this.suggestions = [];
    },
    submitForm() {
        if(!this.form.name) { alert('Wajib memilih nama obat dari suggestion!'); return; }
        
        fetch('/api/rs/medications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(this.form)
        }).then(res => res.json()).then(data => {
            alert(data.message);
            this.showModal = false;
            this.initData(); // Reload tabel obat setelah disave
        });
    },
    deleteItem(id) { 
        if(confirm('Hapus obat ini dari daftar master? (Data laporan lama di dashboard akan tetap aman)')) { 
            fetch('/api/rs/medications/' + id, { 
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => this.initData()); 
        } 
    }
}" x-init="initData()">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-capsules text-blue-600"></i> Master Data Obat Apotek RS</h2>
        <button @click="openCreate()" class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-sm"><i class="fa-solid fa-plus"></i> Registrasi Stok Obat</button>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                    <th class="p-3">Nama Formula Obat</th>
                    <th class="p-3">Golongan / Khasiat</th>
                    <th class="p-3">Stok (Unit)</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y text-gray-700">
                <template x-if="items.length === 0">
                    <tr><td colspan="4" class="p-4 text-center text-gray-400 text-xs">Belum ada data obat di database. Silakan klik tambah obat.</td></tr>
                </template>
                <template x-for="item in items" :key="item.id">
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-3 font-semibold text-gray-900" x-text="item.name"></td>
                        <td class="p-3 text-gray-600" x-text="item.type"></td>
                        <td class="p-3 font-mono font-bold text-emerald-600" x-text="item.stock + ' Pcs'"></td>
                        <td class="p-3 flex justify-center gap-2">
                            <button @click="deleteItem(item.id)" class="text-red-600 text-xs font-semibold px-2"><i class="fa-solid fa-trash"></i> Hapus</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div x-show="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50" x-transition style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border" @click.away="showModal = false">
            <h3 class="text-md font-bold text-gray-800 mb-4">Tambah Stok Formularium Obat</h3>
            <form @submit.prevent="submitForm()" class="space-y-4">
                <div class="relative">
                    <label class="block text-xs font-bold text-gray-500 mb-1">CARI NAMA FORMULA GENERIK</label>
                    <input type="text" x-model="searchQuery" @input="updateSearch()" class="w-full border p-2.5 rounded-lg text-sm" placeholder="Ketik: para, amox, omep...">
                    
                    <div x-show="suggestions.length > 0" class="absolute left-0 right-0 bg-white border mt-1 rounded-xl shadow-xl z-50 max-h-40 overflow-y-auto divide-y text-xs">
                        <template x-for="med in suggestions">
                            <div @click="selectSuggestion(med)" class="p-2.5 hover:bg-blue-50 cursor-pointer transition">
                                <span class="font-bold text-gray-900" x-text="med.name"></span> - <span class="text-gray-500" x-text="med.type"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-0.5">GOLONGAN KLINIS</label>
                    <input type="text" x-model="form.type" readonly class="w-full bg-gray-50 border p-2 rounded-lg text-xs font-medium text-gray-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">JUMLAH STOK AWAL</label>
                    <input type="number" x-model="form.stock" required class="w-full border p-2 rounded-lg text-sm font-mono">
                </div>

                <div class="flex justify-end gap-2 text-xs font-bold">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-400">Batal</button>
                    <button type="submit" :disabled="!form.name" :class="form.name ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'" class="px-5 py-2 rounded-lg">Simpan Obat</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection