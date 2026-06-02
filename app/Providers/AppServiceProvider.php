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

    public function boot(): void
    {
        // Jika diakses lewat domain ngrok, paksa semua link & form memakai HTTPS
        if (str_contains(request()->getHost(), 'ngrok-free.dev')) {
            URL::forceScheme('https');
        }
    }
}