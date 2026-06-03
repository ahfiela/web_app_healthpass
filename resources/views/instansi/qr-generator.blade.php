<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Instansi - Secure QR Code Generator</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-indigo-200">
                    <i class="fa-solid fa-building-shield text-lg"></i>
                </div>
                <div>
                    <h1 class="text-sm font-extrabold text-slate-900 tracking-tight">Gateway Validasi Instansi</h1>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Sistem Verifikasi Kesehatan Terintegrasi</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Server Online
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">
        
        <div class="mb-6">
            <h2 class="text-xl font-extrabold text-slate-900">QR Code Aturan Pengkondisian Sehat</h2>
            <p class="text-xs text-slate-500 mt-1">Tentukan kriteria standar kelainan fisik atau klinis di bawah ini untuk memproduksi kode pemindaian bagi aplikasi Flutter Pasien.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-indigo-600 text-sm"></i>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Konfigurasi Parameter Syarat</h3>
                    </div>
                    <span class="text-[10px] bg-amber-50 text-amber-700 font-bold px-2 py-0.5 rounded border border-amber-200">
                        * Kriteria Bersifat Opsional
                    </span>
                </div>

                <form id="qrForm" action="{{ url('/instansi/qr-generator') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Instansi / Nama Keperluan Seleksi <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-xs">
                                <i class="fa-solid fa-signature"></i>
                            </span>
                            <input type="text" id="namaInstansi" name="nama_instansi" required value="{{ session('instansi') ?? '' }}"
                                class="w-full bg-slate-50/50 border border-slate-200 rounded-xl py-2.5 pl-9 pr-4 text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition" 
                                placeholder="Contoh: PT. Tbk - Seleksi Administrasi">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kekurangan / Kelainan Fisik yang Dilarang</label>
                        <p class="text-[10px] text-slate-400 mb-2">Pilih kondisi spesifik yang tidak boleh dimiliki oleh calon peserta.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 bg-slate-50/50 border border-slate-200 rounded-xl p-3 min-h-[60px]">
                            @forelse($disabilities as $disability)
                                <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-pointer select-none bg-white p-2 rounded-lg border border-slate-100 hover:border-slate-200 transition">
                                    <input type="checkbox" name="forbidden_disabilities[]" value="{{ $disability->id }}" class="kriteria-checkbox w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                    <span class="font-medium text-slate-800">{{ $disability->name }}</span>
                                </label>
                            @empty
                                <div class="col-span-2 text-center py-4 text-slate-400 text-[11px]">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1"></i> Blm menjalankan Seeder Database (`php artisan db:seed`)
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Riwayat Penyakit yang Dilarang (Status Aktif)</label>
                        <p class="text-[10px] text-slate-400 mb-2">Pasien dengan rekam medis klinis aktif di bawah ini otomatis akan ditolak saat scan.</p>
                        
                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 max-h-[160px] overflow-y-auto space-y-2 divide-y divide-slate-100/70 min-h-[60px]">
                            @forelse($diseases as $disease)
                                <label class="flex items-start gap-3 text-xs text-slate-700 pt-2 cursor-pointer select-none first:pt-0">
                                    <input type="checkbox" name="forbidden_icd_codes[]" value="{{ $disease->icd_code }}"
                                        class="kriteria-checkbox w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500 mt-0.5">
                                    <div class="leading-tight">
                                        <span class="font-mono text-red-600 font-bold bg-red-50 px-1.5 py-0.5 rounded text-[10px] border border-red-100">{{ $disease->icd_code }}</span>
                                        <span class="font-medium text-slate-800 ml-1">{{ $disease->name }}</span>
                                    </div>
                                </label>
                            @empty
                                <div class="text-center py-4 text-slate-400 text-[11px]">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1"></i> Blm menjalankan Seeder Database (`php artisan db:seed`)
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Option Bypass Enkripsi -->
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex items-start gap-3 mt-4">
                        <input type="checkbox" name="use_bypass" id="useBypass" value="1" checked class="w-4 h-4 rounded text-amber-600 border-slate-300 focus:ring-amber-500 mt-0.5 cursor-pointer">
                        <div class="leading-tight">
                            <label for="useBypass" class="block text-xs font-bold text-amber-900 cursor-pointer select-none">QR Tanpa Enkripsi (Sangat Direkomendasikan)</label>
                            <p class="text-[10px] text-amber-700 mt-1">Menghasilkan data QR JSON polos dengan tingkat kerapatan rendah (kotak besar) sehingga sangat mudah & cepat di-scan oleh kamera laptop/webcam.</p>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmit" disabled
                        class="w-full bg-slate-200 text-slate-400 font-bold py-3 rounded-xl text-xs flex items-center justify-center gap-2 transition duration-200 cursor-not-allowed">
                        <i class="fa-solid fa-lock text-sm"></i> Pilih Minimal Satu Kriteria Kesehatan
                    </button>
                </form>
            </div>

            <div class="lg:col-span-5 flex flex-col items-center">
                <div class="w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-6 text-center flex flex-col items-center justify-center min-h-[430px]">
                    
                    @if(session('qr_string'))
                        <span class="bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider mb-2">
                            QR Code Aturan Aktif
                        </span>
                        <h4 class="text-sm font-extrabold text-slate-900 max-w-xs truncate mb-5">{{ session('instansi') }}</h4>
                        
                        <div id="qrWrapper" class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm relative flex flex-col items-center justify-center">
                            <div id="qrTarget">
                                {!! QrCode::size(200)->backgroundColor(255,255,255)->color(15, 23, 42)->margin(1)->generate(session('qr_string')) !!}
                            </div>
                            <p class="text-[9px] text-slate-400 font-bold mt-3 tracking-wider uppercase">Scan via Mobile Pasien</p>
                        </div>
                        
                        <!-- Kode Teks Manual untuk Testing -->
                        <div class="w-full mt-4 bg-slate-50 p-3 rounded-xl border border-slate-200 text-left">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kode Enkripsi Manual (Untuk Testing/Emulator):</label>
                            <div class="flex gap-2">
                                <input type="text" readonly id="manualQrString" value="{{ session('qr_string') }}" 
                                    class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-[10px] font-mono text-slate-600 focus:outline-none">
                                <button type="button" onclick="copyManualCode()" 
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold transition">
                                    Salin
                                </button>
                            </div>
                        </div>
                        
                        <div class="w-full mt-6 pt-4 border-t border-slate-100">
                            <button type="button" onclick="downloadQRNative()" 
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl text-xs shadow-md shadow-emerald-100 flex items-center justify-center gap-2 transition duration-150 cursor-pointer">
                                <i class="fa-solid fa-cloud-arrow-down text-sm"></i> Unduh Gambar QR (.png)
                            </button>
                        </div>
                    @else
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 mb-4 text-slate-300">
                            <i class="fa-solid fa-qrcode text-7xl"></i>
                        </div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Menunggu Data Form</h4>
                        <p class="text-[11px] text-slate-400 max-w-xs mt-2 leading-relaxed">
                            Isi nama instansi dan centang minimal satu batasan kekurangan/penyakit di sebelah kiri untuk melahirkan visual QR Code pemindaian.
                        </p>
                    @endif

                </div>
            </div>

        </div>
    </main>

    <script>
        // 1. VALIDASI INTERAKTIF TOMBOL SUBMIT
        const checkboxes = document.querySelectorAll('.kriteria-checkbox');
        const btnSubmit = document.getElementById('btnSubmit');
        const namaInstansiInput = document.getElementById('namaInstansi');

        function validateFormState() {
            let anyChecked = false;
            checkboxes.forEach(cb => { if(cb.checked) anyChecked = true; });

            if (anyChecked && namaInstansiInput.value.trim() !== "") {
                btnSubmit.disabled = false;
                btnSubmit.className = "w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl text-xs shadow-lg shadow-indigo-100 flex items-center justify-center gap-2 transition duration-200 cursor-pointer";
                btnSubmit.innerHTML = `<i class="fa-solid fa-qrcode text-sm"></i> Kunci Aturan & Terbitkan QR Code`;
            } else {
                btnSubmit.disabled = true;
                btnSubmit.className = "w-full bg-slate-200 text-slate-400 font-bold py-3 rounded-xl text-xs flex items-center justify-center gap-2 transition duration-200 cursor-not-allowed";
                btnSubmit.innerHTML = `<i class="fa-solid fa-lock text-sm"></i> Pilih Minimal Satu Kriteria Kesehatan`;
            }
        }

        checkboxes.forEach(cb => cb.addEventListener('change', validateFormState));
        namaInstansiInput.addEventListener('input', validateFormState);

        document.addEventListener("DOMContentLoaded", validateFormState);

        // 2. SOLUSI AMAN: DOWNLOAD DENGAN CANVAS NATIVE TANPA LIBRARY LUAR
        function downloadQRNative() {
            // Ambil elemen SVG yang di-render oleh SimpleQrCode Laravel
            const svgElement = document.querySelector('#qrTarget svg');
            if (!svgElement) {
                alert("Gagal menemukan data QR Code.");
                return;
            }

            // Serialisasi data SVG ke format string XML
            const svgString = new XMLSerializer().serializeToString(svgElement);
            const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
            
            // Konfigurasi dimensi gambar resolusi tinggi (Agar tidak pecah saat di-scan)
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            const size = 600; // Ukuran pixel output PNG
            canvas.width = size;
            canvas.height = size;

            const image = new Image();
            image.onload = function() {
                // Beri latar belakang putih bersih pada Canvas sebelum menggambar QR
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, size, size);
                
                // Gambar QR Code di atas background putih
                context.drawImage(image, 0, 0, size, size);
                
                // Trigger download file PNG otomatis
                const link = document.createElement('a');
                const namaFile = namaInstansiInput.value.trim().replace(/\s+/g, '_');
                link.download = `QR_Syarat_${namaFile}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            };

            // Mengubah blob menjadi URL sumber gambar untuk di-render canvas
            image.src = URL.createObjectURL(svgBlob);
        }

        function copyManualCode() {
            const copyText = document.getElementById("manualQrString");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            alert("Kode aturan berhasil disalin!");
        }
    </script>
</body>
</html>