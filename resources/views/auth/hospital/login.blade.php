<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Tenant - MED-PORTAL RS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex items-center justify-center h-screen font-sans">
    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm max-w-sm w-full">
        <div class="text-center mb-6">
            <div class="h-12 w-12 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-white text-xl mx-auto mb-3 shadow-sm">
                <i class="fa-solid fa-hospital"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Portal Tenant RS</h2>
            <p class="text-xs text-slate-500 mt-1">Masuk ke dashboard manajemen internal Rumah Sakit Anda</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-50 text-rose-700 rounded-xl text-xs border border-rose-100 flex items-start space-x-2">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('hospital.login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1 tracking-wide">EMAIL RESMI</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-sm">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required value="{{ old('email') }}" placeholder="nama@rumahsakit.com" class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50/50 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1 tracking-wide">PASSWORD</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50/50 transition">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-xl text-sm transition shadow-sm cursor-pointer mt-2 flex items-center justify-center space-x-2">
                <span>Masuk Dashboard</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">Rumah Sakit belum terintegrasi? <a href="{{ route('hospital.register') }}" class="text-blue-600 font-semibold hover:underline">Daftar Tenant Baru</a></p>
        </div>
    </div>
</body>
</html>