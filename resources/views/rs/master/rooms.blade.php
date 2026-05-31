@extends('rs.layout.app')

@section('content')
<div x-data="{ 
    items: [], showModal: false, isEdit: false,
    form: { id: '', room_code: '', name: '' },

    initData() { 
        fetch('/api/rs/rooms').then(res => res.json()).then(data => this.items = data); 
    },
    openCreate() { 
        this.isEdit = false; 
        this.form = { id: '', room_code: '', name: '' }; 
        this.showModal = true; 
    },
    openEdit(item) { 
        this.isEdit = true; 
        this.form = { ...item }; 
        this.showModal = true; 
    },
    submitForm() {
        let url = this.isEdit ? '/api/rs/rooms/' + this.form.id : '/api/rs/rooms';
        let method = this.isEdit ? 'PUT' : 'POST';
        fetch(url, { 
            method: method, 
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, 
            body: JSON.stringify(this.form) 
        }).then(() => { this.showModal = false; this.initData(); });
    },
    deleteItem(id) { 
        if(confirm('Hapus ruangan ini?')) { 
            fetch('/api/rs/rooms/' + id, { 
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => this.initData()); 
        } 
    }
}" x-init="initData()">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-door-open text-blue-600"></i> Master Ruangan / Poli</h2>
        <button @click="openCreate()" class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-sm"><i class="fa-solid fa-plus"></i> Tambah Ruangan</button>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase border-b">
                    <th class="p-3">Kode Ruangan</th>
                    <th class="p-3">Nama Poliklinik / Kamar</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y text-gray-700">
                <template x-if="items.length === 0">
                    <tr><td colspan="3" class="p-4 text-center text-gray-400 text-xs">Belum ada data ruangan.</td></tr>
                </template>
                <template x-for="item in items" :key="item.id">
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-3 font-mono font-bold text-blue-600" x-text="item.room_code"></td>
                        <td class="p-3 font-semibold text-gray-900" x-text="item.name"></td>
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
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border" @click.away="showModal = false">
            <h3 class="text-md font-bold text-gray-800 mb-4"><span x-text="isEdit ? 'Edit Ruangan' : 'Tambah Ruangan'"></span></h3>
            <form @submit.prevent="submitForm()" class="space-y-4">
                <div><label class="block text-xs font-bold text-gray-500 mb-1">KODE RUANGAN</label><input type="text" x-model="form.room_code" required class="w-full border p-2 rounded-lg text-sm"></div>
                <div><label class="block text-xs font-bold text-gray-500 mb-1">NAMA LAYANAN POLI</label><input type="text" x-model="form.name" required class="w-full border p-2 rounded-lg text-sm"></div>
                <div class="flex justify-end gap-2 text-xs font-bold"><button type="button" @click="showModal = false" class="px-4 py-2 text-gray-400">Batal</button><button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Simpan</button></div>
            </form>
        </div>
    </div>
</div>
@endsection