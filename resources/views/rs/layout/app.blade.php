<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MED-PORTAL RS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <div class="flex h-screen overflow-hidden">
        <div class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between hidden md:flex">
            <div class="p-5 flex flex-col h-full justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-8 pb-4 border-b border-gray-100">
                        <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-white text-lg shadow-md shadow-blue-500/20 shrink-0">
                            {{ strtoupper(substr(Auth::guard('hospital')->user()->nama_rs ?? 'M', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <h1 class="text-sm font-bold tracking-tight text-gray-900 uppercase truncate">
                                {{ Auth::guard('hospital')->user()->nama_rs ?? 'MED-PORTAL RS' }}
                            </h1>
                            <p class="text-[10px] text-blue-600 font-bold tracking-wider uppercase mt-0.5">
                                FASKES: {{ Auth::guard('hospital')->user()->kode_rs ?? 'GLOBAL' }}
                            </p>
                        </div>
                    </div>
                    
                    @php $current_route = Request::path(); @endphp
                    <nav class="space-y-1">
                        <a href="/rs/dashboard" class="flex items-center gap-3 py-2.5 px-4 rounded-lg font-medium transition text-gray-600 hover:bg-blue-50 hover:text-blue-600 {{ $current_route == 'rs/dashboard' ? 'bg-blue-600/10 text-blue-600 font-semibold' : '' }}">
                            <i class="fa-solid fa-chart-pie w-5"></i> Dashboard Stats
                        </a>
                        <a href="/rs/visits" class="flex items-center gap-3 py-2.5 px-4 rounded-lg font-medium transition text-gray-600 hover:bg-blue-50 hover:text-blue-600 {{ $current_route == 'rs/visits' ? 'bg-blue-600/10 text-blue-600 font-semibold' : '' }}">
                            <i class="fa-solid fa-bell w-5"></i> Validasi Kunjungan
                        </a>
                        
                        <div class="pt-6 mt-4 border-t border-gray-100 text-xs text-gray-400 uppercase tracking-wider font-bold px-4">Master Data CRUD</div>
                        <a href="/rs/master/doctors" class="flex items-center gap-3 py-2 px-4 rounded-lg text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 {{ $current_route == 'rs/master/doctors' ? 'bg-blue-600/10 text-blue-600 font-semibold' : '' }}">
                            <i class="fa-solid fa-user-doctor w-5"></i> Data Dokter
                        </a>
                        <a href="/rs/master/rooms" class="flex items-center gap-3 py-2 px-4 rounded-lg text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 {{ $current_route == 'rs/master/rooms' ? 'bg-blue-600/10 text-blue-600 font-semibold' : '' }}">
                            <i class="fa-solid fa-door-open w-5"></i> Data Ruangan
                        </a>
                        <a href="/rs/master/diseases" class="flex items-center gap-3 py-2 px-4 rounded-lg text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 {{ $current_route == 'rs/master/diseases' ? 'bg-blue-600/10 text-blue-600 font-semibold' : '' }}">
                            <i class="fa-solid fa-virus-covid w-5"></i> Data Penyakit
                        </a>
                        <a href="/rs/master/medications" class="flex items-center gap-3 py-2 px-4 rounded-lg text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 {{ $current_route == 'rs/master/medications' ? 'bg-blue-600/10 text-blue-600 font-semibold' : '' }}">
                            <i class="fa-solid fa-capsules w-5"></i> Data Obat Apotek
                        </a>
                    </nav>
                </div>

                <div class="mt-auto pt-4 border-t border-gray-100 flex flex-col gap-3">
                    <div class="bg-gray-50 px-3 py-2 rounded-xl border border-gray-100 flex items-center justify-between">
                        <div class="flex flex-col min-w-0">
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Petugas</span>
                            <span class="text-xs text-gray-700 font-semibold truncate">{{ Auth::guard('hospital')->user()->name ?? 'Administrator' }}</span>
                        </div>
                        <form action="{{ route('hospital.logout') }}" method="POST" class="inline shrink-0">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-all border border-rose-100 cursor-pointer flex items-center justify-center" title="Keluar Sistem">
                                <i class="fa-solid fa-power-off"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-gray-100 text-xs text-gray-400 flex items-center gap-2 bg-gray-50">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                Sistem Online Terbuka
            </div>
        </div>

        <div class="flex-1 flex flex-col overflow-y-auto">
            <header class="bg-white py-4 px-6 flex justify-between items-center border-b border-gray-200 sticky top-0 z-10">
                <div class="text-md font-semibold text-gray-700">Sistem Integrasi Rekam Medis & Mobile Pasien</div>
                <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    Koneksi API Server Aktif
                </div>
            </header>

            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });
    </script>
</body>
</html>