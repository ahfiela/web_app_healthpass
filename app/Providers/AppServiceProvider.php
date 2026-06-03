<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot()
{
    // Tambahkan baris kode ini untuk memotong jalur penulisan folder lokal ke folder sementara Vercel
    if (config('app.env') === 'production') {
        $paths = [
            '/tmp/framework/sessions',
            '/tmp/framework/cache',
            '/tmp/framework/views',
            '/tmp/storage/bootstrap/cache',
            '/tmp/storage/framework/cache',
        ];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
}

}