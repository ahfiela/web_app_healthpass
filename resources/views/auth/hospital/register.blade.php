<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Tenant - MED-PORTAL RS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex items-center justify-center h-screen font-sans">
    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm max-w-md w-full">
        <div class="text-center mb-6">
            <div class="h-12 w-12 rounded-xl bg-slate-900 flex items-center justify-center font-bold text-white text-xl mx-auto mb-3 shadow-sm">
                <i class="fa-solid fa-file-medical"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Registrasi Tenant Baru</h2>
            <p class="text-xs text-slate-500 mt-1">Sistem akan memvalidasi Kode RS Anda ke server pusat secara otomatis</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-50 text-rose-700 rounded-xl text-xs border border-rose-100 flex items-start space-x-2">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('hospital.register.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1 tracking-wide">KODE RS RESMI (Validasi Pusat)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-500 text-sm">
                        <i class="fa-solid fa-key"></i>
                    </span>
                    <input type="text" name="kode_rs" required placeholder="Contoh: RS-PMI atau RSUD-CIAWI" value="{{ old('kode_rs') }}" class="w-full pl-9 pr-3 py-2 border border-blue-200 rounded-xl text-sm font-mono font-bold text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-blue-50/20 placeholder:font-sans placeholder:font-normal uppercase transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1 tracking-wide">NAMA ADMIN PETUGAS</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-sm">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="name" required placeholder="Nama Lengkap Anda" value="{{ old('name') }}" class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-slate-50/50 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1 tracking-wide">EMAIL REKENING RS</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-sm">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required placeholder="admin@rs-pmi.com" value="{{ old('email') }}" class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-slate-50/50 transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1 tracking-wide">PASSWORD</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-slate-50/50 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1 tracking-wide">KONFIRMASI</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-slate-50/50 transition">
                </div>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 rounded-xl text-sm transition mt-2 shadow-sm cursor-pointer flex items-center justify-center space-x-2">
                <i class="fa-solid fa-network-wired"></i>
                <span>Validasi & Daftarkan Tenant</span>
            </button>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">Sudah memiliki akun tenant? <a href="{{ route('hospital.login') }}" class="text-blue-600 font-semibold hover:underline">Masuk Ke Portal</a></p>
        </div>
    </div>
</body>
</html>