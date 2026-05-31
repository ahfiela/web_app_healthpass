@extends('rs.layout.app')

@section('content')
<div x-data="{ 
    items: [], 
    showModal: false, 
    isEdit: false,
    form: { id: '', nip: '', name: '', specialist: '' },

    initData() {
        fetch('/api/rs/doctors')
            .then(res => res.json())
            .then(data => this.items = data)
            .catch(err => console.error('Error load dokter:', err));
    },
    openCreate() {
        this.isEdit = false;
        this.form = { id: '', nip: '', name: '', specialist: '' };
        this.showModal = true;
    },
    openEdit(item) {
        this.isEdit = true;
        let rawName = item.name.startsWith('dr. ') ? item.name.replace('dr. ', '') : item.name;
        this.form = { id: item.id, nip: item.nip, name: rawName, specialist: item.specialist };
        this.showModal = true;
    },
    submitForm() {
        if (!this.form.name.toLowerCase().startsWith('dr. ')) {
            this.form.name = 'dr. ' + this.form.name;
        }
        
        let url = this.isEdit ? '/api/rs/doctors/' + this.form.id : '/api/rs/doctors';
        let method = this.isEdit ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(this.form)
        }).then(res => {
            if(res.ok) {
                this.showModal = false;
                this.initData();
            } else {
                alert('Gagal menyimpan data. Pastikan NIP unik!');
            }
        });
    },
    deleteItem(id) {
        if(confirm('Apakah Anda yakin ingin menghapus dokter ini dari daftar master?')) {
            fetch('/api/rs/doctors/' + id, { 
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => this.initData());
        }
    }
}" x-init="initData()">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-user-doctor text-blue-600"></i> Master Data Dokter
        </h2>
        <button @click="openCreate()" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-sm flex items-center gap-1.5 transition">
            <i class="fa-solid fa-plus"></i> Tambah Dokter
        </button>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                    <th class="p-3">NIP</th>
                    <th class="p-3">Nama Dokter</th>
                    <th class="p-3">Spesialisasi</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y text-gray-700">
                <template x-if="items.length === 0">
                    <tr><td colspan="4" class="p-4 text-center text-gray-400 text-xs">Belum ada data dokter di database.</td></tr>
                </template>
                <template x-for="item in items" :key="item.id">
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-3 font-mono" x-text="item.nip"></td>
                        <td class="p-3 font-semibold text-gray-900" x-text="item.name"></td>
                        <td class="p-3 text-gray-600" x-text="item.specialist"></td>
                        <td class="p-3 flex justify-center gap-2">
                            <button @click="openEdit(item)" class="text-blue-600 hover:bg-blue-50 px-2.5 py-1 rounded-md text-xs font-semibold"><i class="fa-solid fa-pen"></i> Edit</button>
                            <button @click="deleteItem(item.id)" class="text-red-600 hover:bg-red-50 px-2.5 py-1 rounded-md text-xs font-semibold"><i class="fa-solid fa-trash"></i> Hapus</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div x-show="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50" x-transition style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border" @click.away="showModal = false">
            <h3 class="text-md font-bold text-gray-800 mb-4">
                <span x-text="isEdit ? 'Edit Data Dokter' : 'Tambah Dokter Baru'"></span>
            </h3>
            <form @submit.prevent="submitForm()" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">NIP DOKTER</label>
                    <input type="text" x-model="form.nip" required class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">NAMA LENGKAP</label>
                    <div class="flex items-center border rounded-lg bg-gray-50 overflow-hidden">
                        <span class="bg-gray-100 px-3 py-2 text-sm text-gray-500 font-bold border-r">dr.</span>
                        <input type="text" x-model="form.name" required class="w-full bg-white p-2 text-sm outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">SPESIALISASI</label>
                    <input type="text" x-model="form.specialist" required class="w-full border p-2 rounded-lg text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2 text-xs font-bold">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-400">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection